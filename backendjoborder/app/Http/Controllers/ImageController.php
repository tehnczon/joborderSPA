<?php

use App\Models\JobOrderImage;
use Illuminate\Support\Facades\Storage;

class ImageController
{
    public function uploadMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_order_id' => 'required|integer|exists:job_orders,id',
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Image upload validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // ... continue image upload logic
    }

    public function delete($id)
    {
        $image = JobOrderImage::find($id);

        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // Delete DB record
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }
}
