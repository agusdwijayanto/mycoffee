<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::latest()->paginate(15);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function setujui(Testimonial $testimonial)
    {
        $testimonial->update(['disetujui' => true]);

        return back()->with('sukses', 'Testimoni dari '.$testimonial->nama.' disetujui dan tampil di situs.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('sukses', 'Testimoni berhasil dihapus.');
    }
}
