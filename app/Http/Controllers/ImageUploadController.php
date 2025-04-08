namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->file('image')) {
            $imagePath = $request->file('image')->store('uploads', 'public');
            return response()->json(['path' => $imagePath], 200);
        }

        return response()->json(['error' => 'Image upload failed'], 400);
    }
}
