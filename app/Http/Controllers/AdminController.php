<?php

namespace App\Http\Controllers;

use App\Models\certificate;
use Illuminate\Http\Request;
use App\Models\contact_us;
use Illuminate\Support\Facades\Mail;
use App\Models\blog;
use App\Models\Products;

class AdminController extends Controller
{
    function index()
    {
        return view('admin.index');
    }

    function admin_contact()
    {
        $contacts = contact_us::all();
        return view('admin.contact', compact('contacts'));
    }

    public function send_mail(Request $request)
    {
        $request->validate([
            'mail' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $data = [
            'mail' => $request->input('mail'),
            'subject' => $request->input('subject'),
            'messageContent' => $request->input('message')
        ];

        try {
            Mail::send('admin.contact_mail_send', $data, function ($message) use ($data) {
                $message->to($data['mail']);
                $message->subject($data['subject']);
            });

            return redirect()->back()->with('success', 'Mail sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send mail: ' . $e->getMessage());
        }
    }
    public function delete_contact($id)
    {
        try {
            $contact = contact_us::findOrFail($id);
            $contact->delete();
            return redirect()->back()->with('d_success', 'Contact deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('d_error', 'Failed to delete contact: ' . $e->getMessage());
        }
    }

    function admin_certificate()
    {
        $certificates = certificate::all();
        return view('admin.certificate', compact('certificates'));
    }

    public function insert_certificate(Request $request)
    {
        $request->validate([
            'certificate_name' => 'required',
            'certificate_image' => 'required|image|mimes:jpeg,png,jpg|max:20480',
            'certificate_description' => 'required',
        ], [
            'certificate_name.required' => 'The certificate name field is required.',
            'certificate_image.required' => 'The certificate image field is required.',
            'certificate_image.image' => 'The certificate image must be an image file.',
            'certificate_image.mimes' => 'The certificate image must be a file of type: jpeg, png, jpg.',
            'certificate_image.max' => 'The certificate image may not be greater than 20480 kilobytes.',
            'certificate_description.required' => 'The certificate description field is required.',
        ]);

        try {
            $certificate = new certificate();
            $certificate->certificate_name = $request->input('certificate_name');
            $certificate->certificate_description = $request->input('certificate_description');

            if ($request->hasFile('certificate_image')) {
                $image = $request->file('certificate_image');
                $imageName = 'certificate_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/certificates'), $imageName);
                $certificate->certificate_image = $imageName;
            }

            $certificate->save();

            return redirect()->back()->with('success', 'Certificate added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add certificate: ' . $e->getMessage());
        }
    }

    public function update_certificate(Request $request, $id)
    {
        $request->validate([
            'certificate_name' => 'required',
            'certificate_image' => 'image|mimes:jpeg,png,jpg|max:20480',
            'certificate_description' => 'required',
        ], [
            'certificate_name.required' => 'The certificate name field is required.',
            'certificate_image.image' => 'The certificate image must be an image file.',
            'certificate_image.mimes' => 'The certificate image must be a file of type: jpeg, png, jpg.',
            'certificate_image.max' => 'The certificate image may not be greater than 20480 kilobytes.',
            'certificate_description.required' => 'The certificate description field is required.',
        ]);

        try {
            $certificate = certificate::findOrFail($id);
            $certificate->certificate_name = $request->input('certificate_name');
            $certificate->certificate_description = $request->input('certificate_description');

            if ($request->hasFile('certificate_image')) {
                // Delete old image
                if ($certificate->certificate_image) {
                    $oldImagePath = public_path('images/certificates/' . $certificate->certificate_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Upload new image
                $image = $request->file('certificate_image');
                $imageName = 'certificate_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/certificates'), $imageName);
                $certificate->certificate_image = $imageName;
            }

            $certificate->save();

            return redirect('admin-certificate')->with('success', 'Certificate updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update certificate: ' . $e->getMessage());
        }
    }

    function edit_certificate($id)
    {
        $GetData = certificate::all();
        $new = certificate::find($id);
        $url = url('certificate/update/' . $id);
        $data = compact('GetData', 'new', 'url');
        return view('admin.certificate_edit', $data);
    }

    function delete_certificate($id)
    {
        try {
            $certificate = certificate::findOrFail($id);

            if ($certificate->certificate_image) {
                $imagePath = public_path('images/certificates/' . $certificate->certificate_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $certificate->delete();

            return redirect()->back()->with('d_success', 'Certificate and associated image deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('d_error', 'Failed to delete certificate: ' . $e->getMessage());
        }
    }

    function admin_blog()
    {
        $blogs = blog::all();
        return view('admin.blog', compact('blogs'));
    }

    public function store_blog(Request $request)
    {
        $message = [
            'title.required' => 'Please write a blog title.',
            'category.required' => 'Please select a category.',
            'content.required' => 'Please write something in the content field.',
            'blog_image.required' => 'Please upload a blog image.',
            'blog_image.mimes' => 'The blog image must be a file of type: jpeg, png, jpg.',
            'blog_image.max' => 'The blog image may not be greater than 20480 kilobytes.',
        ];

        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'content' => 'required',
            'blog_image' => 'required|mimes:jpeg,png,jpg|max:20480',
        ], $message);

        $blog = new blog();
        $blog->title = $request->input('title');
        $blog->category = $request->input('category');
        $blog->content = $request->input('content');
        if ($request->hasFile('blog_image')) {
            $image = $request->file('blog_image');
            $imageName = 'blog_' . date('d-m-Y') . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/blog'), $imageName);
            $blog->image = $imageName;
        }
        $blog->date = date('d-m-Y');

        $blog->save();

        return redirect()->back()->with('success', 'Blog added successfully!');
    }

    //edit blog
    function edit_blog($id)
    {
        $blogs = blog::all();
        $new = blog::find($id);
        $url = url('/update-blog/' . $id);
        $data = compact('blogs', 'new', 'url');
        return view('admin.blog_edit', $data);
    }

    //update blog
    public function update_blog(Request $request, $id)
    {
        $message = [
            'title.required' => 'Please write a blog title.',
            'category.required' => 'Please select a category.',
            'content.required' => 'Please write something in the content field.',
            'blog_image.mimes' => 'The blog image must be a file of type: jpeg, png, jpg.',
            'blog_image.max' => 'The blog image may not be greater than 20480 kilobytes.',
        ];

        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'content' => 'required',
            'blog_image' => 'nullable|mimes:jpeg,png,jpg|max:20480',
        ], $message);

        $blog = blog::findOrFail($id);
        $blog->title = $request->input('title');
        $blog->category = $request->input('category');
        $blog->content = $request->input('content');

        if ($request->hasFile('blog_image')) {
            // Delete old image
            if ($blog->image) {
                $oldImagePath = public_path('images/blog/' . $blog->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Upload new image
            $image = $request->file('blog_image');
            $imageName = 'blog_' . date('d-m-Y') . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/blog'), $imageName);
            $blog->image = $imageName;
        }
        $blog->date = date('d-m-Y');

        $blog->save();

        return redirect('admin-blog')->with('success', 'Blog updated successfully!');
    }

    //delete blog
    public function delete_blog($id)
    {
        try {
            $blog = blog::findOrFail($id);

            // Delete the associated image file
            if ($blog->image) {
                $imagePath = public_path('images/blog/' . $blog->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $blog->delete();
            return redirect()->back()->with('d_success', 'Blog and associated image deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('d_error', 'Failed to delete blog: ' . $e->getMessage());
        }
    }

    function admin_products()
    {
        $products = Products::all();
        return view('admin.products', compact('products'));
    }

    public function store_products(Request $request)
    {
        $message = [
            'name.required' => 'Please write a product name.',
            'description.required' => 'Please write a product description.',
            'product_hs_code.required' => 'Please write a product hs code.',
            'bg_image.required' => 'Please upload a product bg image.',
            'bg_image.mimes' => 'The product background image must be a file of type: jpeg, png, jpg.',
            'bg_image.max' => 'The product background image may not be greater than 20480 kilobytes.',
            'other_image.required' => 'Please upload at least one product other image.',
            'other_image.*.mimes' => 'The product other images must be files of type: jpeg, png, jpg.',
            'other_image.*.max' => 'Each product other image may not be greater than 20480 kilobytes.',
            'other_image.min' => 'Please upload at least three product other images.',
            'other_image.3' => 'The third product other image failed to upload.', // Custom error message for the third image
        ];
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'product_hs_code' => 'required',
            'bg_image' => 'required|mimes:jpeg,png,jpg|max:20480',
            'other_image' => 'required|array|min:3', // Ensure at least 3 images
            'other_image.*' => 'mimes:jpeg,png,jpg|max:20480', // Allow multiple images
        ], $message);

        $product = new Products();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->product_hs_code = $request->input('product_hs_code');

        if ($request->hasFile('bg_image')) {
            $image = $request->file('bg_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $product->bg_image = $imageName;
        }

        if ($request->hasFile('other_image')) {
            $otherImages = $request->file('other_image');
            $imageNames = [];
            $errorMessages = [];

            foreach ($otherImages as $index => $image) {
                $imageNameOther = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension(); // Ensure unique names
                if ($image->isValid()) {
                    $image->move(public_path('images/products'), $imageNameOther);
                    $imageNames[] = $imageNameOther; // Store each image name
                } else {
                    $errorMessages[] = "The product other image '{$imageNameOther}' failed to upload.";
                }
            }

            if (!empty($errorMessages)) {
                return redirect()->back()->withErrors(['other_image' => $errorMessages]);
            }

            $product->other_image = json_encode($imageNames); // Store as JSON
        }

        $product->save();

        if ($product) {
            return redirect()->back()->with('success', 'Product added successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to add product.');
        }
    }

    public function delete_product($id)
    {
        $product = Products::findOrFail($id);

        // Delete the product images from the storage
        if ($product->bg_image) {
            $bgImagePath = public_path('images/products/' . $product->bg_image);
            if (file_exists($bgImagePath)) {
                unlink($bgImagePath);
            }
        }

        if ($product->other_image) {
            $otherImages = json_decode($product->other_image);
            foreach ($otherImages as $image) {
                $otherImagePath = public_path('images/products/' . $image);
                if (file_exists($otherImagePath)) {
                    unlink($otherImagePath);
                }
            }
        }

        $product->delete();
        return redirect()->back()->with('d_success', 'Product deleted successfully.');
    }

    function auth_lockscreen()
    {
        return view('admin.auth-lockscreen');
    }

    function auth_login()
    {
        return view('admin.auth-login');
    }

    function auth_register()
    {
        return view('admin.auth-register');
    }

    function calendar_full()
    {
        return view('admin.calendar-full');
    }

    function calendar_list()
    {
        return view('admin.calendar-list');
    }

    function chart_apex()
    {
        return view('admin.chart-apex');
    }

    function chart_c3()
    {
        return view('admin.chart-c3');
    }

    function chart_chartlist()
    {
        return view('admin.chart-chartist');
    }

    function chart_chartjs()
    {
        return view('admin.chart-chartjs');
    }

    function chart_flot()
    {
        return view('admin.chart-flot');
    }

    function chart_knob()
    {
        return view('admin.chart-knob');
    }

    function chart_morris()
    {
        return view('admin.chart-morris');
    }

    function chart_sparkline()
    {
        return view('admin.chart-sparkline');
    }

    function form_autonumeric()
    {
        return view('admin.form-autonumeric');
    }

    function form_editors()
    {
        return view('admin.form-editors');
    }

    function form_element()
    {
        return view('admin.form-element');
    }

    function form_file_upload()
    {
        return view('admin.form-file-upload');
    }

    function form_input_groups()
    {
        return view('admin.form-input-groups');
    }

    function form_inputmask()
    {
        return view('admin.form-inputmask');
    }

    function form_layout()
    {
        return view('admin.form-layouts');
    }

    function form_listbox()
    {
        return view('admin.form-listbox');
    }

    function form_pickers()
    {
        return view('admin.form-pickers');
    }

    function form_range_slider()
    {
        return view('admin.form-range-slider');
    }

    function form_selects()
    {
        return view('admin.form-selects');
    }

    function form_switchers()
    {
        return view('admin.form-switchers');
    }

    function form_validation()
    {
        return view('admin.form-validation');
    }

    function icons_cryptocurrency()
    {
        return view('admin.icons-cryptocurrency');
    }

    function icons_dash()
    {
        return view('admin.icons-dash');
    }

    function icons_drip()
    {
        return view('admin.icons-drip');
    }

    function icons_feather()
    {
        return view('admin.icons-feather');
    }

    function icons_fontawesome()
    {
        return view('admin.icons-fontawesome');
    }

    function icons_ion()
    {
        return view('admin.icons-ion');
    }

    function icons_material()
    {
        return view('admin.icons-material');
    }

    function icons_themify()
    {
        return view('admin.icons-themify');
    }

    function icons_weather()
    {
        return view('admin.icons-weather');
    }

    function index_car_dealer()
    {
        return view('admin.index-car-dealer');
    }

    function index_crm()
    {
        return view('admin.index-crm');
    }

    function index_crypto_currency()
    {
        return view('admin.index-crypto-currency');
    }

    function index_dating()
    {
        return view('admin.index-dating');
    }

    function index_ecommerce()
    {
        return view('admin.index-ecommerce');
    }

    function index_job_portal()
    {
        return view('admin.index-job-portal');
    }

    function index_real_estate()
    {
        return view('admin.index-real-estate');
    }

    function index_stock_market()
    {
        return view('admin.index-stock-market');
    }

    function layout_mini()
    {
        return view('admin.layout-mini');
    }

    function mail_inbox()
    {
        return view('admin.mail-inbox');
    }

    function maps_google()
    {
        return view('admin.maps-google');
    }

    function maps_mapael()
    {
        return view('admin.maps-mapael');
    }

    function maps_vactor()
    {
        return view('admin.maps-vactor');
    }

    function page_404()
    {
        return view('admin.page-404');
    }

    function page_500()
    {
        return view('admin.page-500');
    }

    function page_account_settings()
    {
        return view('admin.page-account-settings');
    }

    function page_clients()
    {
        return view('admin.page-clients');
    }

    function page_coming_soon()
    {
        return view('admin.page-coming-soon');
    }

    function page_contacts()
    {
        return view('admin.page-contacts');
    }

    function page_employees()
    {
        return view('admin.page-employees');
    }

    function page_faq()
    {
        return view('admin.page-faq');
    }

    function page_file_manager()
    {
        return view('admin.page-file-manager');
    }

    function page_gallary()
    {
        return view('admin.page-gallary');
    }

    function page_pricing()
    {
        return view('admin.page-pricing');
    }

    function page_task()
    {
        return view('admin.page-task');
    }

    function tables_basic()
    {
        return view('admin.tables-basic');
    }

    function tables_color()
    {
        return view('admin.tables-color');
    }

    function tables_datatables()
    {
        return view('admin.tables-datatable');
    }

    function tables_editable()
    {
        return view('admin.tables-editable');
    }

    function tables_export()
    {
        return view('admin.tables-export');
    }

    function ui_accordions()
    {
        return view('admin.ui-accordions');
    }

    function ui_alert()
    {
        return view('admin.ui-alert');
    }

    function ui_badges()
    {
        return view('admin.ui-badges');
    }

    function ui_button_block()
    {
        return view('admin.ui-button-block');
    }

    function ui_button_groups()
    {
        return view('admin.ui-button-groups');
    }

    function ui_button_icon()
    {
        return view('admin.ui-button-icon');
    }

    function ui_button_social()
    {
        return view('admin.ui-button-social');
    }

    function ui_button()
    {
        return view('admin.ui-button');
    }

    function ui_cards()
    {
        return view('admin.ui-cards');
    }

    function ui_carousel()
    {
        return view('admin.ui-carousel');
    }

    function ui_dropdowns()
    {
        return view('admin.ui-dropdowns');
    }

    function ui_grid()
    {
        return view('admin.ui-grid');
    }

    function ui_lightbox()
    {
        return view('admin.ui-lightbox');
    }

    function ui_list_group()
    {
        return view('admin.ui-list-group');
    }

    function ui_media()
    {
        return view('admin.ui-media');
    }

    function ui_modals()
    {
        return view('admin.ui-modals');
    }

    function ui_nav()
    {
        return view('admin.ui-nav');
    }

    function ui_nestable_list()
    {
        return view('admin.ui-nestable-list');
    }

    function ui_paginition()
    {
        return view('admin.ui-pagination');
    }

    function ui_progressbars()
    {
        return view('admin.ui-progressbars');
    }

    function ui_sweet_alert()
    {
        return view('admin.ui-sweet-alert');
    }

    function ui_tabs()
    {
        return view('admin.ui-tabs');
    }

    function ui_toastr()
    {
        return view('admin.ui-toastr');
    }

    function ui_tooltips_popovers()
    {
        return view('admin.ui-tooltips-popovers');
    }

    function ui_typography()
    {
        return view('admin.ui-typography');
    }

    function ui_video()
    {
        return view('admin.ui-video');
    }

    function widget_chart()
    {
        return view('admin.widget-chart');
    }

    function widget_list()
    {
        return view('admin.widget-list');
    }

    function widget_social()
    {
        return view('admin.widget-social');
    }
}
