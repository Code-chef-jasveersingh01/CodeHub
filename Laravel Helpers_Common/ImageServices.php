<?php

namespace App\Services\Common;

use App\Models\Media;
use App\Models\AttachedFileMedia;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ImageServices
{
    public function saveImage(object $image, string $path, bool $defaultSave = false)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            if ($image->isValid() && in_array($image->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                $image_extension    = $image->getClientOriginalExtension();
                $image_size         = $image->getSize();
                $type               = $image->getMimeType();
                $new_name           = rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' . $image_extension;
                $thumbnail_name     = 'thumbnail_' . rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' .  $image_extension;

                #save thumbnail
                $thumbnail = Image::make($image->getRealPath());

                $thumbnail->resize(400, 400, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($stroage_path . '/' . $thumbnail_name);

                #save original
                $image->move($stroage_path, $new_name);

                $media_data = [
                    'type' => $type,
                    'file_size' => $image_size,
                    'name' => $new_name,
                    'thumbnail_name' => $thumbnail_name,
                ];

                if ($defaultSave) {
                    $media = $this->saveMediaData($media_data);
                    return !empty($media) ? $media->id : null;
                } else {
                    return $media_data;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveImage() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveImageBase64(string $image, string $path, bool $defaultSave = false)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            if ($image) {
                $image_extension    = explode('/', mime_content_type($image))[1];
                $image_size         = (int)(strlen(rtrim($image, '=')) * 0.75);
                $mimeData           = getimagesize($image);
                $type               = $mimeData['mime'];
                $new_name           = rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' . $image_extension;
                $thumbnail_name     = 'thumbnail_' . rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' .  $image_extension;

                #save original
                $thumbnail = Image::make($image)->save($stroage_path . '/' . $new_name);

                #save thumbnail
                $thumbnail->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($stroage_path  . '/' . $thumbnail_name);

                $media_data = [
                    'type' => $type,
                    'file_size' => $image_size,
                    'name' => $new_name,
                    'thumbnail_name' => $thumbnail_name,
                ];

                if ($defaultSave) {
                    $media = $this->saveMediaData($media_data);
                    return !empty($media) ? $media->id : null;
                } else {
                    return $media_data;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveImageBase64() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveImageBase64Intervention(string $imageBase64, string $path, bool $defaultSave = false)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            #process image data
            $image          = Image::make($imageBase64);
            $type           = $image->mime();
            $extension      = substr($type, 6);
            $new_name       = rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' . $extension;
            $thumbnail_name = 'thumbnail_' . rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' .  $extension;

            #save original
            $image->save($stroage_path . '/' . $new_name);

            $image_size = $image->filesize();

            #save thumbnail
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($stroage_path . '/' . $thumbnail_name);

            $media_data = [
                'type'           => $type,
                'file_size'      => $image_size,
                'name'           => $new_name,
                'thumbnail_name' => $thumbnail_name,
            ];

            if ($defaultSave) {
                $media = $this->saveMediaData($media_data);
                return !empty($media) ? $media->id : null;
            } else {
                return $media_data;
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveImageBase64Intervention() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveImageWithOutDB(object $image, string $path)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            if ($image->isValid()) {
                $image_name = rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . $image->getClientOriginalName();
                $image->move($stroage_path, $image_name);
                return $image_name;
            } else {
                return '';
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveImageWithOutDB() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveImageBase64WithOutDB(string $image, string $path)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            if ($image) {
                $image_extension    = explode('/', mime_content_type($image))[1];
                $new_name           = rand(1000000000, 9999999999) . date('mdYHis') . uniqid() . '.' . $image_extension;

                #save original
                Image::make($image)->save($stroage_path . '/' . $new_name);

                return $new_name;
            } else {
                Log::error('#### ImageServices -> saveImageBase64WithOutDB() ####');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveImageBase64WithOutDB() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveAnyAttachedFileBase64(object $file, string $path, bool $defaultSave = false)
    {
        try {
            #create stroage path
            $stroage_path   = base_path($path);

            if ($file) {
                $type      = $file->type;
                $fileSize  = $file->size;
                $name      = time() . '_' . $file->name;

                #save file
                $fileStroagePath = $stroage_path . '/' . $name.'jpg';
                $decoded_file = base64_decode($file->data);
                file_put_contents($fileStroagePath, $decoded_file);

                $file_data = [
                    'type'          => $type,
                    'file_size'     => $fileSize,
                    'name'          => $name,
                ];

                if ($defaultSave) {
                    $fileData = $this->saveFileData($file_data);
                    return !empty($fileData) ? $fileData->id : null;
                } else {
                    return $file_data;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveAnyAttachedFileBase64() #### ' . $e->getMessage());
            return null;
        }
    }

    public function saveMediaData(array $media_data)
    {
        try {
            return Media::create($media_data);
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveMediaData() ####  ' . $e->getMessage());
            return null;
        }
    }

    public function saveFileData(array $fileArray)
    {
        try {
            return AttachedFileMedia::create($fileArray);
        } catch (\Exception $e) {
            Log::error('#### ImageServices -> saveFileData() ####  ' . $e->getMessage());
            return null;
        }
    }
}