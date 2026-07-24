<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\JobVacancy;
use App\Models\OnboardingTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RecruitmentController extends Controller
{
    /**
     * Recruitment Dashboard - overview stats + pipeline (3a, 3b)
     */
    public function dashboard()
    {
        $totals = [
            'open_vacancies' => JobVacancy::where('status', 'open')->count(),
            'total_applicants' => Applicant::count(),
            'in_interviews' => Applicant::where('status', 'interview')->count(),
            'offers_extended' => JobOffer::count(),
            'hired' => Applicant::where('status', 'hired')->count(),
        ];

        $openVacancies = JobVacancy::withCount('applicants')
            ->where('status', 'open')
            ->orderByDesc('posted_date')
            ->take(4)
            ->get();

        $recentApplicants = Applicant::with('jobVacancy')
            ->orderByDesc('applied_at')
            ->take(5)
            ->get();

        $upcomingInterviews = Interview::with('applicant')
            ->where('interview_date', '>=', now())
            ->orderBy('interview_date')
            ->take(4)
            ->get();

        return view('recruitment.dashboard', compact('totals', 'openVacancies', 'recentApplicants', 'upcomingInterviews'));
    }

    /**
     * Job Vacancies - list + create (3a)
     */
    public function vacancies()
    {
        $user = auth()->user();
        $isHr = in_array($user->role, ['admin', 'hr_manager']);

        $vacancyQuery = JobVacancy::with('department')->withCount('applicants');

        if (! $isHr) {
            $vacancyQuery->whereRaw('LOWER(status) = ?', ['open'])
                ->where(function ($query) {
                    $query->whereNull('closing_date')
                        ->orWhereDate('closing_date', '>=', today());
                });
        }

        $vacancies = $vacancyQuery->orderByDesc('posted_date')->paginate(10)->withQueryString();
        $departments = $isHr ? Department::orderBy('name')->get() : collect();

        $employee = $user->employee;
        $appliedVacancyIds = $employee
            ? Applicant::where('employee_id', $employee->id)->pluck('job_vacancy_id')->all()
            : [];

        return view('recruitment.vacancies', compact('vacancies', 'departments', 'isHr', 'appliedVacancyIds', 'employee'));
    }

    /**
     * Existing employees can apply directly to an open internal vacancy.
     * The existing employee record is reused, so the applicant is linked
     * back to the employee without duplicating personal information.
     */
    public function applyToVacancy(JobVacancy $vacancy)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('success', 'Your account is not linked to an employee record yet. Contact HR before applying.');
        }

        if (strtolower((string) $vacancy->status) !== 'open' || ($vacancy->closing_date && $vacancy->closing_date->isBefore(today()))) {
            return back()->with('success', 'This vacancy is no longer accepting applications.');
        }

        if (Applicant::where('employee_id', $employee->id)->where('job_vacancy_id', $vacancy->id)->exists()) {
            return back()->with('success', 'You have already applied for this vacancy.');
        }

        Applicant::create([
            'job_vacancy_id' => $vacancy->id,
            'employee_id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'phone' => $employee->phone_number,
            'date_of_birth' => $employee->date_of_birth,
            'gender' => $employee->gender,
            'status' => 'applied',
            'applied_at' => today(),
        ]);

        return back()->with('success', "Your application for {$vacancy->title} has been submitted.");
    }

    public function storeVacancy(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employment_type' => 'nullable|in:Full-time,Part-time,Contractual',
            'location' => 'nullable|string|max:255',
            'posted_date' => 'required|date',
            'closing_date' => 'nullable|date|after_or_equal:posted_date',
        ]);
        $validated['status'] = 'open';

        JobVacancy::create($validated);

        return redirect()->route('recruitment.vacancies')->with('success', 'Job vacancy posted.');
    }

    public function closeVacancy(JobVacancy $vacancy)
    {
        $vacancy->update(['status' => 'closed']);

        return back()->with('success', 'Vacancy closed.');
    }

    /**
     * Applicants - list + create (3b)
     */
    public function applicants(Request $request)
    {
        $query = Applicant::with('jobVacancy');

        if ($vacancyId = $request->input('job_vacancy_id')) {
            $query->where('job_vacancy_id', $vacancyId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhereRaw("(first_name || ' ' || last_name) LIKE ?", [$like]);
            });
        }

        $applicants = $query->orderByDesc('applied_at')->paginate(10)->withQueryString();
        $vacancies = JobVacancy::orderByDesc('posted_date')->get();

        return view('recruitment.applicants', compact('applicants', 'vacancies'));
    }

    public function createApplicant()
    {
        $vacancies = JobVacancy::where('status', 'open')->orderBy('title')->get();

        return view('recruitment.applicant-create', compact('vacancies'));
    }

    public function storeApplicant(Request $request)
    {
        $validated = $request->validate([
            'job_vacancy_id' => 'required|exists:job_vacancies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'applied_at' => 'required|date',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validated['resume_path'] = $path;
            $validated['resume_file_name'] = $request->file('resume')->getClientOriginalName();
        }
        $validated['status'] = 'applied';

        $applicant = Applicant::create($validated);

        return redirect()->route('recruitment.applicants.show', $applicant)->with('success', 'Applicant added.');
    }

    /**
     * Applicant Evaluation & Job Offer page - the combined workflow screen (3b, 3c, 3d)
     */
    public function showApplicant(Applicant $applicant)
    {
        $applicant->load(['jobVacancy.department', 'interviews', 'offer', 'onboardingTasks']);

        return view('recruitment.applicant-show', compact('applicant'));
    }

    public function updateApplicantStatus(Request $request, Applicant $applicant)
    {
        $request->validate([
            'status' => 'required|in:applied,screening,interview,offered,hired,rejected',
        ]);

        $applicant->update(['status' => $request->input('status')]);

        return back()->with('success', 'Applicant status updated.');
    }

    /**
     * Schedule/record an interview (3b)
     */
    public function storeInterview(Request $request, Applicant $applicant)
    {
        if (in_array($applicant->status, ['offered', 'hired', 'rejected'])) {
            return back()->with('success', 'A decision has already been made for this applicant - interviews can no longer be recorded.');
        }

        $validated = $request->validate([
            'stage' => 'nullable|string|max:255',
            'interviewer' => 'nullable|string|max:255',
            'interview_date' => 'required|date',
            'score' => 'nullable|numeric|min:0|max:5',
            'feedback' => 'nullable|string',
            'result' => 'nullable|in:Passed,Failed,Pending',
        ]);

        Interview::create($validated + ['applicant_id' => $applicant->id]);

        if ($applicant->status === 'applied' || $applicant->status === 'screening') {
            $applicant->update(['status' => 'interview']);
        }

        return back()->with('success', 'Interview recorded.');
    }

    /**
     * Issue a job offer (3c)
     */
    public function storeOffer(Request $request, Applicant $applicant)
    {
        if ($applicant->offer && in_array($applicant->offer->status, ['accepted', 'declined'])) {
            return back()->with('success', 'This offer has already been ' . $applicant->offer->status . ' and can no longer be revised.');
        }

        $validated = $request->validate([
            'offered_position' => 'nullable|string|max:255',
            'employment_type' => 'nullable|in:Full-time,Part-time,Contractual',
            'salary_offered' => 'required|numeric|min:0',
            'offer_date' => 'required|date',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $validated['status'] = 'pending';

        JobOffer::updateOrCreate(
            ['applicant_id' => $applicant->id],
            $validated
        );

        $applicant->update(['status' => 'offered']);

        return back()->with('success', 'Job offer issued.');
    }

    /**
     * Accept or decline an existing offer - accepting seeds the onboarding checklist (3d)
     */
    public function updateOfferStatus(Request $request, JobOffer $offer)
    {
        $request->validate(['status' => 'required|in:accepted,declined']);

        $offer->load('applicant');

        // If the applicant is already an employee, the employee must make
        // the decision from their own employee account. HR cannot accept or
        // decline the offer on the employee's behalf.
        if ($offer->applicant?->employee_id) {
            return back()->with('success', 'This offer is linked to an existing employee account. The employee must accept or decline it from Job Offers.');
        }

        $offer->update(['status' => $request->input('status')]);
        $applicant = $offer->applicant;

        if ($request->input('status') === 'accepted') {
            $applicant->update(['status' => 'hired']);

            // Seed a standard onboarding checklist (3d) if not already seeded
            if ($applicant->onboardingTasks()->count() === 0) {
                $defaultTasks = [
                    'Offer accepted by candidate',
                    'Personal information collected',
                    'Documents submitted',
                    'Background verification',
                    'IT account setup',
                    'Welcome & orientation schedule',
                ];
                foreach ($defaultTasks as $task) {
                    OnboardingTask::create([
                        'applicant_id' => $applicant->id,
                        'task_name' => $task,
                        // the offer-acceptance step is complete the moment we get here
                        'is_completed' => $task === 'Offer accepted by candidate',
                        'completed_at' => $task === 'Offer accepted by candidate' ? now() : null,
                    ]);
                }
            }
        } else {
            $applicant->update(['status' => 'rejected']);
        }

        return back()->with('success', 'Offer status updated.');
    }

    /**
     * Toggle a single onboarding checklist item (3d)
     */
    public function toggleOnboardingTask(OnboardingTask $task)
    {
        $task->update([
            'is_completed' => ! $task->is_completed,
            'completed_at' => ! $task->is_completed ? now() : null,
        ]);

        return back()->with('success', 'Checklist updated.');
    }

    /**
     * Convert a fully onboarded applicant into an actual Employee record -
     * the concrete link between Recruitment (Function 3) and Employee Records (Function 1).
     */
    public function convertToEmployee(Applicant $applicant)
    {
        // Issue 2 fix: guard against converting the same applicant twice
        if ($applicant->employee_id) {
            return redirect()->route('employees.edit', $applicant->employee)
                ->with('success', 'This applicant was already converted to an employee record.');
        }

        if ($applicant->status !== 'hired') {
            return back()->with('success', 'Only hired applicants can be converted to an employee record.');
        }

        $lastEmployee = Employee::orderByDesc('id')->first();
        $nextNumber = 'EMP-' . str_pad((string) (($lastEmployee->id ?? 0) + 1), 4, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'department_id' => $applicant->jobVacancy->department_id,
            'employee_number' => $nextNumber,
            'first_name' => $applicant->first_name,
            'last_name' => $applicant->last_name,
            'email' => $applicant->email,
            'phone_number' => $applicant->phone,
            // Issue 1 fix: carry over whatever the applicant record actually has
            'date_of_birth' => $applicant->date_of_birth,
            'gender' => $applicant->gender,
            'job_title' => optional($applicant->offer)->offered_position ?? $applicant->jobVacancy->title,
            'contract_type' => optional($applicant->offer)->employment_type ?? $applicant->jobVacancy->employment_type,
            'employment_status' => 'active',
            'hire_date' => optional($applicant->offer)->start_date ?? now(),
        ]);

        // Issue 2 fix: mark this applicant as converted so the button disappears
        // and a second click can't create a duplicate employee record
        $applicant->update(['employee_id' => $employee->id]);

        // Issue 1 fix: land on the Edit page (not the profile) with a clear
        // prompt, since a job application never collects civil status, full
        // address, government numbers, or emergency contact - HR completes
        // those here rather than the applicant form pretending to be a full
        // employee intake form.
        return redirect()->route('employees.edit', $employee)
            ->with('success', "Employee record created for {$employee->full_name}. Please complete civil status, address, government numbers, and emergency contact before saving.");
    }

    /**
     * Interviews - all-applicants list, for the sidebar "Interviews" page
     */
    public function interviewsIndex()
    {
        $interviews = Interview::with('applicant.jobVacancy')
            ->orderByDesc('interview_date')
            ->paginate(15);

        return view('recruitment.interviews', compact('interviews'));
    }

    /**
     * Offers - all-applicants list, for the sidebar "Offers" page
     */
    public function offersIndex()
    {
        $offers = JobOffer::with('applicant.jobVacancy')
            ->orderByDesc('offer_date')
            ->paginate(15);

        return view('recruitment.offers', compact('offers'));
    }

    /**
     * Employee self-service: view job offers linked to the employee's
     * existing employee record.
     */
    public function myOffers()
    {
        $employee = auth()->user()->employee;

        $offers = $employee
            ? JobOffer::with('applicant.jobVacancy')
                ->whereHas('applicant', fn ($query) => $query->where('employee_id', $employee->id))
                ->orderByDesc('offer_date')
                ->get()
            : collect();

        return view('recruitment.my-offers', compact('offers', 'employee'));
    }

    /**
     * Employee self-service: accept or decline only their own pending offer.
     */
    public function respondToMyOffer(Request $request, JobOffer $offer)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(403, 'Your account is not linked to an employee record.');
        }

        $result = DB::transaction(function () use ($request, $offer, $employee) {
            $lockedOffer = JobOffer::with('applicant')
                ->whereKey($offer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedOffer->applicant || $lockedOffer->applicant->employee_id !== $employee->id) {
                abort(403, 'You can only respond to your own job offers.');
            }

            if ($lockedOffer->status !== 'pending') {
                return 'already_' . $lockedOffer->status;
            }

            $status = $request->input('status');
            $lockedOffer->update(['status' => $status]);

            $lockedOffer->applicant->update([
                'status' => $status === 'accepted' ? 'hired' : 'rejected',
            ]);

            if ($status === 'accepted' && $lockedOffer->applicant->onboardingTasks()->count() === 0) {
                $defaultTasks = [
                    'Offer accepted by candidate',
                    'Personal information collected',
                    'Documents submitted',
                    'Background verification',
                    'IT account setup',
                    'Welcome & orientation schedule',
                ];

                foreach ($defaultTasks as $task) {
                    OnboardingTask::create([
                        'applicant_id' => $lockedOffer->applicant->id,
                        'task_name' => $task,
                        'is_completed' => $task === 'Offer accepted by candidate',
                        'completed_at' => $task === 'Offer accepted by candidate' ? now() : null,
                    ]);
                }
            }

            return $status;
        });

        if (str_starts_with($result, 'already_')) {
            return back()->with('success', 'This job offer has already been ' . str_replace('already_', '', $result) . '.');
        }

        return back()->with('success', 'Your job offer has been ' . $result . '.');
    }
}
