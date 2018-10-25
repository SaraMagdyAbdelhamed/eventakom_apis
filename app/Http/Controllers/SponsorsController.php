<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use App\Sponsor;


class SponsorsController extends Controller
{



	public function list_sponsors(Request $request)
	{
		$sponsors = Sponsor::orderBy('created_at','DESC')->take(4)->get();
		return Helpers::Get_Response(200,'success','',[],$sponsors);

		

	}





}


