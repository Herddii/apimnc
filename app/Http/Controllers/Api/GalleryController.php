<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\Models\Mediakit\Programperiode; 
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 
use Validator;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Intervention\Image\ImageManager;
use Response;
use Illuminate\Pagination\Paginator;
use App\Models\Mediakit\Contentnew;

class GalleryController extends Controller 
{
public $successStatus = 200;
/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 

    public function get_gallery_content(Request $request){
        $id_genre = request('name');
        $token = request('token');
        $prog = request('prog');
            $content = \DB::table('tbl_program_periode as a')->selectRaw('a.id_program, b.program_name, b.id_genre ,c.id_content,d.genre_name, c.content_file_download, a.updated_at, a.content_start_date ,e.username,
            g.filetype_name, g.folder, g.id_filetype, g.ext_file')
            ->leftJoin('tbl_program as b','b.id_program','a.id_program')
            ->leftJoin('tbl_content_copp as c','c.id_program_periode','a.id_program_periode')
            ->leftJoin('tbl_program_genre as d','d.id_genre','b.id_genre')
            ->leftJoin('tbl_user as e','e.USER_ID','a.update_user')
            ->leftJoin('db_m_channel as f','f.id_channel','e.ID_BU')
            ->leftJoin('tbl_filetype as g','g.id_filetype','c.id_filetype');
            if($id_genre!=20){
            $content=$content->where('b.id_genre',$id_genre);  
            } else {
                $content=$content->where('b.id_genre','like','%%');
            }
            $content = $content->where('c.id_master_filetype',8)
            ->orderBy('b.program_name')->where('c.mediakit',1)->get();
            
        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function get_bu(Request $request){
        $token = request('token');
        $prog = request('prog');
            $content = \DB::table('tbl_bu as a')->selectRaw('*')
            ->where('a.active',1)->get();
            
        return response($content,200); 
    }

    public function get_gallery_all_program(Request $request,$idGenre){
        $g = Auth::user();
        $token = request('token');
        $prog = request('prog');
        $bu=1;
        $file = [];
        $content = [];
        $data = [];

        $content = \DB::table('tbl_program_periode as a')->selectRaw('
            a.id_program,
            b.program_name, 
            b.id_genre ,
            d.id_content,
            c.genre_name, 
            d.content_file_download, 
            d.updated_at, 
            d.created_at,
            e.filetype_name, 
            e.folder, 
            e.id_filetype, 
            e.ext_file,
            d.mediakit,
            d.id_bu,
            d.deleted_at,
            f.BU_SHORT_NAME,
            monthname(d.created_at) as bulan,
            concat(if(round(week(d.created_at)/12)=0,1,round(week(d.created_at)/12)),",",monthname(d.created_at)," ",year(d.created_at)) as week')
            ->leftJoin('tbl_program as b','b.id_program','a.id_program')
            ->leftJoin('tbl_program_genre as c','c.id_genre','b.id_genre')
            ->leftJoin('tbl_content as d','d.id_program_periode','a.id_program_periode')
            ->leftJoin('tbl_filetype as e','e.id_filetype','d.id_filetype')
            ->leftJoin('tbl_bu as f','f.id_bu','d.id_bu')
            ->where('a.active',1)
            ->where('e.folder','public')
            ->where('d.id_bu',1)
            ->where('d.id_master_filetype',8);
            if($g->ID_BU == 10 || $g->ID_BU == 11){
                $content = $content->whereIn('b.id_bu',[1,2,3,8]);
                            
            } else {
                $content = $content->where('b.id_bu',$g->ID_BU);
            }

            if($idGenre != 20){
                $content = $content->where('b.id_genre',$idGenre);
            } else {
                $content = $content->where('b.id_genre','like','%%');
            }
            $content = $content->orderBy('d.created_at','DESC')
                            ->orderBy('b.program_name','ASC')->paginate(30);

            foreach ($content as $value => $key) {

            $a = $key->content_file_download;
            $base64 ='http://172.18.11.11/mam1.1/uploads/'.$key->folder.'/'.$key->id_filetype.'/'.$key->content_file_download;

            if(strpos($a, '.jpg') == true || strpos($a, '.JPG') == true || strpos($a,'.png') == true || strpos($a,'.jpeg') == true){
               $header_response = get_headers($base64, 1);
               if ( strpos( $header_response[0], "404" ) == false ){
                 $img = Image::make($base64);
                 $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->response('data-url');
                 array_push($file,$img->encoded);
                } else {
                    array_push($file,'no-image');
                }
            } else {
                array_push($file,$base64);
            }
            array_push($data,[
                'id_program' => $key->id_program,
                'program_name' => $key->program_name,
                'id_genre' => $key->id_genre,
                'id_content' => $key->id_content,
                'genre_name' => $key->genre_name,
                'content_file_download' => $key->content_file_download,
                'updated_at' => $key->updated_at,
                'created_at' => $key->created_at,
                'folder' => $key->folder,
                'id_filetype' => $key->id_filetype,
                'filetype_name' => $key->filetype_name,
                'ext_file' => $key->ext_file,
                'id_bu' => $key->id_bu,
                'mediakit' => $key->mediakit,
                'BU_SHORT_NAME' => $key->BU_SHORT_NAME,
                'week' => $key->week,
                'bulan' => $key->bulan,
                'image' => $file[$value]
            ]);
        }

        return response($data,200)->header('Content-Type', 'application/json','Bearer '.$token);;
    }

    public function get_image(Request $request){
        $g = Auth::user();
        $token = request('token');
        $bu=1;
        $idProgram = request('idProgram');
        $contentFileDownload = request('contentFileDownload');
        $contentIdFiletype = request('contentIdFiletype');
        $id_genre = request('idProgram');
        $content = \DB::table('tbl_content as a')->selectRaw('*')
        ->where('id_content',$idProgram)->get();

        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function get_gallery_all(Request $request){

        $g = Auth::user();
        $bulan = array();
        $bu = 1;
        $file = [];
        $content = [];
        $data = [];
        $token = request('token');
        $query = '(select * from (
        select a.id_content,
        a.id_program_periode,
        a.deleted_at,
        a.id_master_filetype, 
        a.content_title, 
        a.content_file_download,
        a.updated_at,
        a.created_at,
        b.folder,
        b.id_filetype,
        b.ext_file,
        a.id_bu,
        c.title,
        a.mediakit,
        d.BU_SHORT_NAME,
        concat(week(a.updated_at), "-", year(a.updated_at)) as weeky,
        concat(monthname(a.updated_at),",",year(a.updated_at)) as bulan,
        concat(round(week(a.updated_at)/12)+1,",",monthname(a.updated_at)," ",year(a.updated_at)) as week
        from tbl_content as a
        left join tbl_filetype b on b.id_filetype=a.id_filetype
        left join tbl_master_filetype c on c.id_master_filetype = a.id_master_filetype
        left join tbl_bu d on d.id_bu = a.id_bu
        where b.folder="public" and b.ext_file!="url" and a.id_master_filetype in (1,2,8)
        order by a.updated_at desc) as data2) as total';
        

        $testing = \DB::table(\DB::raw($query))
        ->where('total.content_file_download','not like','%#%')
        ->whereNull('total.deleted_at')
        ->where('total.content_file_download','not like','% %')
        ->where('total.updated_at','!=','0000-00-00 00:00:00')
        ->where('total.mediakit',1)
        ->orderBy('total.updated_at','desc');

        $testing = $testing->paginate(30);

        foreach ($testing as $value => $key) {
            // array_push

            $a = $key->content_file_download;
            $base64 ='http://172.18.11.11/mam1.1/uploads/'.$key->folder.'/'.$key->id_filetype.'/'.$key->content_file_download;

            if(strpos($a, '.jpg') == true || strpos($a, '.JPG') == true || strpos($a,'.png') == true || strpos($a,'.jpeg') == true){
               $header_response = get_headers($base64, 1);
               if ( strpos( $header_response[0], "404" ) == false ){
                 $img = Image::make($base64);
                 $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->response('data-url');
                 array_push($file,$img->encoded);
                } else {
                    array_push($file,'no-image');
                }
            } else {
                array_push($file,$base64);
            }
            array_push($data,[
                'id_content'             => $key->id_content,
                'id_program_periode'     => $key->id_program_periode,
                'deleted_at'             => $key->deleted_at,
                'id_master_filetype'     => $key->id_master_filetype,
                'content_title'          => $key->content_title,
                'content_file_download'  => $key->content_file_download,
                'updated_at'             => $key->updated_at,
                'created_at'             => $key->created_at,
                'folder'                 => $key->folder,
                'id_filetype'            => $key->id_filetype,
                'ext_file'               => $key->ext_file,
                'id_bu'                  => $key->id_bu,
                'title'                  => $key->title,
                'mediakit'               => $key->mediakit,
                'BU_SHORT_NAME'          => $key->BU_SHORT_NAME,
                'weeky'                  => $key->weeky,
                'week'                   => $key->week,
                'bulan'                  => $key->bulan,
                'image'                  => $file[$value]
            ]);
        }

        return response($data,200)->header('Content-Type', 'application/json','Bearer '.$token); 

    }

    public function get_special_offers(Request $request){
        $g = Auth::user();
        $bu=1;
        $token = request('token');
        $id_genre = request('name');
        $file = [];
        $content = [];
        $data = [];
        $special_offers = \DB::table('tbl_salestools as a')->selectRaw(' 
            b.id_content,
            a.id_salestools,
            a.id_master_filetype, 
            b.content_title, 
            b.content_file_download,
            b.updated_at,
            b.created_at,
            c.folder,
            b.deleted_at,
            c.id_filetype,
            c.ext_file,
            a.id_bu,
            b.mediakit,
            d.BU_SHORT_NAME,
            concat(monthname(b.created_at),",",year(b.created_at)) as bulan,
            concat(round(week(b.created_at)/12)+1,",",monthname(b.created_at)," ",year(b.created_at)) as week')
        ->leftJoin('tbl_content as b','b.id_program_periode','a.id_salestools')
        ->leftJoin('tbl_filetype as c','c.id_filetype','b.id_filetype')
        ->leftJoin('tbl_bu as d','d.id_bu','b.id_bu')
        ->where('b.id_filetype',14)
        ->where('b.id_master_filetype','<>',8)
        ->where('a.active',1)
        ->where('c.folder','public')
        ->where('a.id_master_filetype',2)
        ->orWhere('b.id_filetype',152)
        ->where('b.id_master_filetype','<>',8)
        ->where('a.active',1)
        ->where('c.folder','public')
        ->where('a.id_master_filetype',2);
        if($g->ID_BU == 10 || $g->ID_BU == 11){
                $special_offers = $special_offers->whereIn('a.id_bu',[1,2,3,8]);
                            
            } else {
                $special_offers = $special_offers->where('a.id_bu',$g->ID_BU);
            }
        $special_offers = $special_offers->orderBy('b.created_at','desc')->paginate(30);

         foreach ($special_offers as $value => $key) {

            $a = $key->content_file_download;
            $base64 ='http://172.18.11.11/mam1.1/uploads/'.$key->folder.'/'.$key->id_filetype.'/'.$key->content_file_download;

            if(strpos($a, '.jpg') == true || strpos($a, '.JPG') == true || strpos($a,'.png') == true || strpos($a,'.jpeg') == true){
               $header_response = get_headers($base64, 1);
               if ( strpos( $header_response[0], "404" ) == false ){
                 $img = Image::make($base64);
                 $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->response('data-url');
                 array_push($file,$img->encoded);
                } else {
                    array_push($file,'no-image');
                }
            } else {
                array_push($file,$base64);
            }
            array_push($data,[
                'id_content'             => $key->id_content,
                'id_salestools'          => $key->id_salestools,
                'deleted_at'             => $key->deleted_at,
                'id_master_filetype'     => $key->id_master_filetype,
                'content_title'          => $key->content_title,
                'content_file_download'  => $key->content_file_download,
                'updated_at'             => $key->updated_at,
                'created_at'             => $key->created_at,
                'folder'                 => $key->folder,
                'id_filetype'            => $key->id_filetype,
                'ext_file'               => $key->ext_file,
                'id_bu'                  => $key->id_bu,
                'mediakit'               => $key->mediakit,
                'BU_SHORT_NAME'          => $key->BU_SHORT_NAME,
                'week'                   => $key->week,
                'bulan'                  => $key->bulan,
                'image'                  => $file[$value]
            ]);
        }


        return response($data,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function get_rate_card(Request $request){
        $g = Auth::user();
        $bu=1;
        $token = request('token');
        $id_genre = request('name');
        $file = [];
        $content = [];
        $data = [];
        $rate_card = \DB::table('tbl_salestools as a')->selectRaw(' 
            b.id_content,
            a.id_salestools,
            a.id_master_filetype, 
            b.content_title, 
            b.content_file_download,
            b.updated_at,
            b.created_at,
            c.folder,
            c.id_filetype,
            c.ext_file,
            b.deleted_at,
            a.id_bu,
            b.mediakit,
            d.BU_SHORT_NAME,
            concat(monthname(b.created_at),",",year(b.created_at)) as bulan,
            concat(round(week(b.created_at)/12)+1,",",monthname(b.created_at)," ",year(b.created_at)) as week')
        ->leftJoin('tbl_content as b','b.id_program_periode','a.id_salestools')
        ->leftJoin('tbl_filetype as c','c.id_filetype','b.id_filetype')
        ->leftJoin('tbl_bu as d','d.id_bu','b.id_bu')
        ->where('b.id_filetype',85)
        ->where('b.id_master_filetype','<>',8)
        ->where('a.content_use','<>',0)
        ->where('a.active',1)
        ->where('c.folder','public')
        ->where('a.id_master_filetype',1)
        ->orWhere('b.id_filetype',16)
        ->where('b.id_master_filetype','<>',8)
        ->where('a.content_use','<>',0)
        ->where('a.active',1)
        ->where('c.folder','public')
        ->where('a.id_master_filetype',1);
        if($g->ID_BU == 10 || $g->ID_BU == 11){
                $rate_card = $rate_card->whereIn('a.id_bu',[1,2,3,8]);
                            
            } else {
                $rate_card = $rate_card->where('a.id_bu',$g->ID_BU);
            }
        $rate_card = $rate_card->orderBy('b.created_at','desc')->paginate(30);

         foreach ($rate_card as $value => $key) {
            // array_push

            $a = $key->content_file_download;
            $base64 ='http://172.18.11.11/mam1.1/uploads/'.$key->folder.'/'.$key->id_filetype.'/'.$key->content_file_download;

            if(strpos($a, '.jpg') == true || strpos($a, '.JPG') == true || strpos($a,'.png') == true || strpos($a,'.jpeg') == true){
               $header_response = get_headers($base64, 1);
               if ( strpos( $header_response[0], "404" ) == false ){
                 $img = Image::make($base64);
                 $img->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->response('data-url');
                 array_push($file,$img->encoded);
                } else {
                    array_push($file,'no-image');
                }
            } else {
                array_push($file,$base64);
            }
            array_push($data,[
                'id_content'             => $key->id_content,
                'id_salestools'          => $key->id_salestools,
                'deleted_at'             => $key->deleted_at,
                'id_master_filetype'     => $key->id_master_filetype,
                'content_title'          => $key->content_title,
                'content_file_download'  => $key->content_file_download,
                'updated_at'             => $key->updated_at,
                'created_at'             => $key->created_at,
                'folder'                 => $key->folder,
                'id_filetype'            => $key->id_filetype,
                'ext_file'               => $key->ext_file,
                'id_bu'                  => $key->id_bu,
                'mediakit'               => $key->mediakit,
                'BU_SHORT_NAME'          => $key->BU_SHORT_NAME,
                'week'                   => $key->week,
                'bulan'                  => $key->bulan,
                'image'                  => $file[$value]
            ]);
        }

        return response($data,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function get_genre_program(Request $request){
        $g = Auth::user();
        $token = request('token');
        $id_genre = request('name');
        $content = \DB::select('select a.id_genre, a.genre_name from tbl_program_genre as a where a.active = 1');
        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
$input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
        $success['token'] =  $user->createToken('MyApp')-> accessToken; 
        $success['name'] =  $user->name;
return response()->json(['success'=>$success], $this-> successStatus); 
    }
/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this-> successStatus); 
    } 
}