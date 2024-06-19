<?php

namespace App\Http\Controllers\Api\V1\User;

use Gate;
use App\Ticket;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Admin\TicketResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Traits\MediaUploadingTrait;


/**
 * @group Tickets Api
 *
 * APIs for managing tickets
 */
class TicketsApiController extends Controller
{
    use MediaUploadingTrait;



     * @response 200 {"message": "Created Successfully", "data": ""}
     * @response 202 {"message": "Updated Successfully", "data": ""}
     * @response 400 {"message": "validation failure message"}
     * @response 401 {"message": "Unauthorised"}
     * @response 404 {"message": "Record Not Found"}
     * @response 500 {"message": "Whoops, looks like something went wrong"}
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'company_code' => 'required|string|max:60',
            'status_id' => 'required|integer|exists:statuses,id',
            'priority_id' => 'required|integer|exists:priorities,id',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => 0, 'message' => $validator->getMessageBag()->first()], Response::HTTP_BAD_REQUEST);
        }

        try{

            $ticket = Ticket::create($request->all());

            // if ($request->hasFile('attachments')) {

            //     $file = $request->file('attachments');

            //     $this->saveMediaFile($file,$ticket,false);
            // }

            foreach($request->input('files') as $file){
               $saveFiles =  $this->save($file['data'],$ticket);
            }

            return response()->json(["status" => 1, "message" => __('message.ticket_created_successfully'),'data' => $ticket ],Response::HTTP_CREATED);

        }catch(\Exception $e){
            Log::error('####### TicketsApiController -> store() #######  ' . $e->getMessage());
            return response()->json(["status" => 0, "message" => __('message.something_went_wrong')],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function save($base64Image,$model)
    {
        if (!$tmpFileObject = $this->validateBase64($base64Image, ['png', 'jpg', 'jpeg', 'gif', 'pdf'])) {
            return response()->json([
                'error' => 'Invalid image format.'
            ], 415);
        }

        $storedFilePath = $this->storeFile($tmpFileObject);

        if(!$storedFilePath) {
            return response()->json([
                'error' => 'Something went wrong, the file was not stored.'
            ], 500);
        }

        $model->addMedia(storage_path('app/public/' . $storedFilePath))->toMediaCollection('attachments');

        return response()->json([
            'image_url' => url(Storage::url($storedFilePath)),
        ]);


        return response()->json([
            'error' => 'Invalid request.'
        ], 400);

    }

    public function validateBase64(string $base64data, array $allowedMimeTypes)
    {
        // strip out data URI scheme information (see RFC 2397)
        if (str_contains($base64data, ';base64')) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            return false;
        }

        // decoding and then re-encoding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }

        $fileBinaryData = base64_decode($base64data);

        // temporarily store the decoded data on the filesystem to be able to use it later on
        $tmpFileName = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFileName, $fileBinaryData);

        $tmpFileObject = new File($tmpFileName);

        // guard against invalid mime types
        $allowedMimeTypes = Arr::flatten($allowedMimeTypes);

        // if there are no allowed mime types, then any type should be ok
        if (empty($allowedMimeTypes)) {
            return $tmpFileObject;
        }

        // Check the mime types
        $validation = Validator::make(
            ['file' => $tmpFileObject],
            ['file' => 'mimes:' . implode(',', $allowedMimeTypes)]
        );

        if($validation->fails()) {
            return false;
        }

        return $tmpFileObject;
    }

    public function storeFile(File $tmpFileObject)
    {
        $tmpFileObjectPathName = $tmpFileObject->getPathname();

        $file = new UploadedFile(
            $tmpFileObjectPathName,
            $tmpFileObject->getFilename(),
            $tmpFileObject->getMimeType(),
            0,
            true
        );

        $storedFile = $file->store('images/base64', ['disk' => 'public']);

        unlink($tmpFileObjectPathName); // delete temp file

        return $storedFile;
    }

}
