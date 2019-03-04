<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\Models\Mediakit\Programperiode; 
use App\Models\Mediakit\Sector; 
use App\Models\Mediakit\Contentnew; 
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 
use Validator;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class YoutubeController extends Controller  
{
public $successStatus = 200;
/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 

    public function get_benefit(Request $request){
        $id_genre = request('benefit_id');
        $token = request('token');
            $content = \DB::table('benefits as a')->selectRaw('a.id_benefit, a.nama_benefit')
            ->orderBy('a.nama_benefit')->get();
            
        return response($content, 200)->header('Content-Type', 'application/json','Bearer '.$token);
    }

    public function get_typespot(Request $request,$id_benefit){
        $id_genre = request('benefit_id');
        $token = request('token');
            $content = \DB::table('benefit_typespot as a')->selectRaw('a.id_typespot, a.id_benefit, a.nama, a.img');
            if($id_benefit !=20){
                $content=$content->where('a.id_benefit',$id_benefit);
            } else {
                $content=$content->groupby('a.id_benefit');
            }

            $content=$content->orderBy('a.nama')->get();
            
        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token); 
    }

    public function get_video(Request $request, $id_typespot, $id_benefit){

        $g = Auth::user();
        $token = request('token'); 

        $content = \DB::table('tbl_content as a')->selectRaw('a.id_content,b.id_typespot, a.content_file_download, a.content_title, a.update_user, a.updated_at, c.id_benefit')
        ->leftJoin('tbl_filetype as b','b.id_filetype','a.id_filetype')
        ->leftJoin('benefit_typespot as c','c.id_typespot','b.id_typespot')
        ->where('b.id_master_filetype',3)
        ->where('a.mediakit',1);

        // if($g->ID_BU = 11 || $g->ID_BU = 10){
        //     $content = $content->whereIn('a.id_bu',[1,2,3,8]);
        // } else {
        //     $content = $content->where('a.id_bu',1);    
        // }

        if($id_typespot != 1000 ){
            $content=$content->where('b.id_typespot',$id_typespot);
            
        } else if ($id_typespot == 1000) {
            $content=$content->where('b.id_typespot','LIKE','%%');
        }

        if($id_benefit != 20){
            $content = $content->where('c.id_benefit',$id_benefit);
        } else if ($id_benefit == 20){
            $content = $content->where('c.id_benefit','like','%%');
        }


        $content=$content->orderBy('a.id_content')->paginate(30);

        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token);
    }   
    public function filter_youtube(Request $request){
        $sector = request('sector');
        $genre = request('genre');
        $bulan = request('bulan');

        if(empty($sector)){
            $sector='SELECT a.id_sector FROM db_m_sector as a';
        } else {
            $sector=join(",",$sector);
        }


        if(empty($genre)){
            $genre='SELECT a.id_genre FROM tbl_program_genre as a WHERE a.active=1';
        } else {
            $genre=join(",",$genre);
        }

        // return $genre;

        if(empty($bulan)){
            $bulan='SELECT monthname(a.updated_at) as bulan from tbl_content as a WHERE a.id_master_filetype=3';
        } else {
            $bulan="'".ucfirst(join(",",$bulan))."'";
        }

        // return $bulan;

        $content=\DB::select('select a.id_content,b.id_typespot, a.content_file_download, a.content_title, a.update_user, a.updated_at, c.id_benefit, a.updated_at from tbl_content as a left join tbl_filetype as b on b.id_filetype=a.id_filetype left join benefit_typespot as c on c.id_typespot=b.id_typespot where a.id_master_filetype=3 and a.id_sector IN ('.$sector.') and a.id_genre IN ('.$genre.') and monthname(a.updated_at) IN ('.$bulan.') and a.content_title is not null');

        return response($content, 200);
    // $content=Contentnew::selectRaw('*')->where('id_master_filetype',3)->whereIN('id_genre',$genre)->whereIN('id_sector',$sector)->whereIN(monthname('updated_at'),$bulan)->get();
    }
     public function get_video_all(Request $request, $id_benefit){

        $g = Auth::user();
        $token = request('token');

        $content = \DB::table('tbl_content as a')->selectRaw('a.id_content,b.id_typespot, a.content_file_download, a.content_title, a.update_user, a.updated_at, c.id_benefit')
        ->leftJoin('tbl_filetype as b','b.id_filetype','a.id_filetype')
        ->leftJoin('benefit_typespot as c','c.id_typespot','b.id_typespot')
        ->where('b.id_master_filetype',3)
        ->where('a.mediakit',1);

        // if($g->ID_BU = 11 || $g->ID_BU = 10){
        //     $content = $content->whereIn('a.id_bu',[1,2,3,8]);
        // } else {
        //     $content = $content->where('a.id_bu',1);    
        // }
        if($id_benefit != 20){
           $content=$content->where('c.id_benefit',$id_benefit);
        } else {
            $content=$content->where('c.id_benefit','LIKE','%%');
        }
        
        $content=$content->orderBy('a.id_content')->paginate(30);

        return response($content,200)->header('Content-Type', 'application/json','Bearer '.$token);
    }

    public function get_sector(Request $request){
        $content = Sector::selectRaw('*')->orderBy('id_sector')->get();
        return response($content,200);
    }   
}