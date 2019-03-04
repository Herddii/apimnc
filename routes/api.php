<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login_mobile', 'Api\LoginController@login_mobile');
Route::post('register', 'Api\LoginController@register');

Route::post('getgallerycontent','Api\GalleryController@get_gallery_content');
Route::group(['middleware' => 'auth:api'], function(){
	Route::get('details', 'Api\LoginController@details');
	Route::get('get_gallery_all','Api\GalleryController@get_gallery_all');
	Route::get('get_gallery_all_program/{idgenre}','Api\GalleryController@get_gallery_all_program');
	Route::get('get_video_all/{id_benefit}','Api\YoutubeController@get_video_all');
	Route::get('get_rate_card','Api\GalleryController@get_rate_card');
	Route::get('get_special_offer','Api\GalleryController@get_special_offers');
});
Route::post('filter','Api\GalleryController@filter');
Route::post('filter_youtube','Api\YoutubeController@filter_youtube');
Route::get('week','Api\GalleryController@week');
Route::get('get_gallery_all_tambah/{week}/{first_limit}/{jumlah_limit}','Api\GalleryController@get_gallery_all_tambah');
Route::get('get_genre_program','Api\GalleryController@get_genre_program');
Route::get('get_benefit','Api\YoutubeController@get_benefit');
Route::get('get_bu','Api\GalleryController@get_bu');
Route::get('get_video/{id_typespot}/{id_benefit}','Api\YoutubeController@get_video');
Route::get('get_typespot/{id_typespot}','Api\YoutubeController@get_typespot');
Route::get('get_filetype','Api\GalleryController@get_filetype');
Route::post('getgallery','Api\GalleryController@get_gallery');
Route::post('get_image','Api\GalleryController@get_image');
Route::post('get_gallery_all_name','Api\GalleryController@get_gallery_all_name');
Route::post('get_gallery_content','Api\GalleryController@get_gallery_content');
Route::post('get_special_offers_month','Api\GalleryController@get_special_offers_month');
Route::get('get_sector','Api\YoutubeController@get_sector');
