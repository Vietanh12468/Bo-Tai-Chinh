<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image as Intervention;

class FileService
{
    public function uploadAndConvertToWebp($file, $uploadDir, $userId)
    {
        try {
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileNameWebp = time() . '.webp';

            // kiểm tra file đã tồn tại chưa
            while (file_exists($uploadDir . '/' . $fileNameWebp)) {
                // Nếu đã tồn tại, tạo tên file mới
                $fileNameWebp = time() . '_' . uniqid() . '.webp';
            }

            // Tải ảnh lên, chuyển đổi thành WebP và lưu lại
            $image = Intervention::read($file->getRealPath());

            // Trả về đường dẫn của file đã lưu
            $savedPath = $uploadDir . '/' . $fileNameWebp;

            // lưu lại file
            $image->toWebp(90)->save($savedPath);
            
            $dataImage = [
                'name' => $fileNameWebp,
                'path' => $savedPath,
                'mime_type' => mime_content_type($savedPath),
                'size' => filesize($savedPath),
            ];
            if (!empty($userId)) {
                $dataImage['created_by'] = $userId;
            }

            $image = new File($dataImage);

            $image->save();

            return $image->toArray();
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có vấn đề
            Log::error('Image upload and conversion failed: ' . $e->getMessage());
            return false;
        }
    }
}
