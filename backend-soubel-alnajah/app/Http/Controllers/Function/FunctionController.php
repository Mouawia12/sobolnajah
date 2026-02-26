<?php

namespace App\Http\Controllers\Function;

use App\Actions\Function\BuildAgendaPageDataAction;
use App\Actions\Function\BuildGalleryPageDataAction;
use App\Actions\Inscription\ApproveInscriptionByClassroomAction;
use App\Actions\Notification\MarkUserNotificationAsReadAction;
use App\Actions\Notification\SendSchoolCertificateNotificationAction;
use App\Http\Requests\NotifySchoolCertificateRequest;
use App\Models\AgendaScolaire\Grade;

use App\Models\Inscription\Inscription;
use App\Models\Inscription\StudentInfo;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Throwable;

class FunctionController extends Controller
{
    public function __construct(
        private ApproveInscriptionByClassroomAction $approveInscriptionByClassroomAction,
        private BuildAgendaPageDataAction $buildAgendaPageDataAction,
        private BuildGalleryPageDataAction $buildGalleryPageDataAction,
        private SendSchoolCertificateNotificationAction $sendSchoolCertificateNotificationAction,
        private MarkUserNotificationAsReadAction $markUserNotificationAsReadAction
    )
    {
    }

    public function showAgenda($id){
        $data = $this->buildAgendaPageDataAction->execute(Auth::user());
        $view = $data['view'];
        unset($data['view']);

        return view($view, $data);
    }

    public function showGallery(){
        return view('front-end.gallery', $this->buildGalleryPageDataAction->execute());
    }

    // Backward-compatible aliases for legacy method naming.
    public function getAgenda($id){
        return $this->showAgenda($id);
    }

    public function getAlbum(){
        return $this->showGallery();
    }

    public function listAgendaGrades($id = null)
    {
        return Grade::query()->pluck('name_grade', 'id');
    }

    // Backward-compatible alias for legacy route naming.
    public function getGrade($id = null){
        return $this->listAgendaGrades($id);
    }

    public function store($id){
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $schoolId = $this->currentSchoolId();

        try {
            $inscription = Inscription::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->findOrFail($id);
            $this->authorize('approve', $inscription);

            $this->approveInscriptionByClassroomAction->execute($inscription, $schoolId);
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Inscriptions.index');
    }


    public function notify(NotifySchoolCertificateRequest $request,$id){
        try {
            if(auth()->user()){
                $this->sendSchoolCertificateNotificationAction->execute(
                    auth()->user(),
                    (int) $id,
                    (string) $request->year,
                    (string) $request->namefr,
                    (string) $request->namear
                );
            }
            return back()->withSuccess('a');
        }
  
        catch (\Exception $e){
            return redirect()->back()->withErrors(['لقد ارسلت طلب شهادة مدرسية بالفعل !']);
        }
        
    }

    public function markAsRead($id){
        $notify = $this->markUserNotificationAsReadAction->execute((string) $id, (int) Auth::id());

        if (!$notify) {
            abort(404);
        }

        $data['notify'] = $this->notifications();
        $data['StudentInfo'] = StudentInfo::query()->where('user_id', $notify['notifiable_id'])->first();
        $data['arryear'] = $notify['data'];

        return view('admin.StudentSchoolCertificateNotification', $data);
    }

    public function showChangePasswordPage(){
        $data['notify'] = $this->notifications();
        return view('admin.changepassword',$data);
    }

    // Backward-compatible alias for legacy method naming.
    public function changepass(){
        return $this->showChangePasswordPage();
    }















}
