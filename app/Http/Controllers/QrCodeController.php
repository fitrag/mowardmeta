<?php

namespace App\Http\Controllers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->query('data');

        if (!$data) {
            return response('Missing data parameter', 400);
        }

        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 10,
            'outputBase64' => false,
        ]);

        $qrCode = new QRCode($options);

        return response($qrCode->render($data))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
