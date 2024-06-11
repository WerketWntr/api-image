<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Resources\V1\ImageResource;
use App\Models\Album;
use App\Models\Image;
use App\Http\Requests\UpdateImageRequest;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ImageResource::collection(Image::where('user_id', $request->user()->id)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();
        $image = $all;
        unset($all['images']);
        $data = [
            'type' => Image::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => $request->user()->id,

        ];
//???????downloading and saving image
        if (isset($all['album_id'])) {
            $album = Album::find($all['album_id']);
            if ($request->user()->id != $album->user_id) {
                return abort(403, 'Unauthorized');
            }

            $data['album_id'] = $all['album_id'];
        }

        $dir = 'images/' . Str::random() . '/';
        $absolutePath = public_path($dir);
        File::makeDirectory($absolutePath);

        if ($image instanceof UploadedFile) {
            $data['name'] = $image->getClientOriginalname();
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientFileExtension();
            $image->move($absolutePath, $data['name']);

            $originalPath = $absolutePath . $data['name'];
        } else {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $originalPath = $absolutePath . $data['name'];


            copy($image, $absolutePath . $data['name']);
        }
        $data['path'] = $dir . $data['name'];

        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width, $height, $image) = $this->getImageWidthAndHeight($w, $h, $originalPath);

        $resizedFilename = $filename . '-resized.' . $extension;
        $image->resize($width, $height)->save($absolutePath.$resizedFilename);
        $data['output_path'] = $dir.$resizedFilename;

        $imageChange = Image::create($data);

        return new ImageResource($imageChange);


    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Image $image)
    {
        if ($request->user()->id != $image->user_id) {
            return abort(403, 'Unauthorized');
        }

        return new ImageResource($image);
    }

    /**
     * Update the specified resource in storage.
     */
    public function byAlbum(Request $request, Album $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403, 'Unauthorized');
        }

        $where = [
            'album_id' => $album->id,

        ];
        return ImageResource::collection(Image::where($where)->paginate());

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Image $image)
    {
        if ($request->user()->id != $image->user_id) {
            return abort(403, 'Unauthorized');
        }

        $image->delete();
        return response('', 204);
    }

    protected function getImageWidthAndHeight(mixed $w, mixed $h, string $originalPath)
    {
        $image = Image::make($originalPath);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        if (str_ends_with($w, '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            $newWidth = $originalWidth * $ratioW / 100;
            $newHeight = $originalHeight * $ratioH / 100;


        } else {
            $newWidth = (float)$w;

            $newHeight = $h ? (float)$h : $originalHeight * $newWidth / $originalWidth;
        }
        return [$newWidth, $newHeight, $image];

    }
}
