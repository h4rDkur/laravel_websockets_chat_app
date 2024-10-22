<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;
use DB;


use App\Events\sendMessage;
use App\Events\otherChat; 

class chatController extends Controller
{
    
    public function test(){

       event(new otherChat("seomthing"));

    }


    public function getCurrentSubs(){

        $subs = DB::table("conversation")
        ->where([
            ["user_id1","=",Auth::user()->id],
            ["user_id2","=",Auth::user()->id,"or"]
        ])
        ->get();

        foreach($subs as $sub){

            $user_id = $sub->user_id1 == Auth::user()->id ? $sub->user_id2 : $sub->user_id1;
            $sub->user_details = DB::table("users")->where("id", $user_id)->select("id","name","email")->get();

        }

        return json_encode($subs);

    }



    public function myPrivateSubs(){

    	$my_private_subs = DB::table("conversation")
    	->where([
    		["user_id1","=",Auth::user()->id],
    		["user_id2","=",Auth::user()->id,"or"]
    	])
    	->pluck("c_id")->toArray();

    	return json_encode($my_private_subs);

    }	

    //
    public function newConvo(Request $req){

    	//check if room exist 
    	$existing_cave = DB::table("conversation")->where([

    		[ "user_id1",  "=",    Auth::user()->id ],
    		[ "user_id2",  "=",    $req->user_id2   ]
    	])
    	->orWhere([
    		
            [ "user_id1",  "=",    $req->user_id2   ],
    		[ "user_id2",  "=",    Auth::user()->id ]

    	])->first();

    	// 
    	if($existing_cave!=null){

            // make receier of messsage subscribe to this channel
            $datas = ["channel_id"=>$existing_cave->c_id, "user_id2"=>$req->user_id2];
            event(new otherChat($datas));

    		return json_encode($datas);

    	}

    	$caveID = DB::table("conversation")->insertGetId([
    	 
    		"user_id1" => Auth::user()->id,
    		"user_id2" => $req->user_id2

    	]);


        // make receier of messsage subscribe to this channel
        $datas = ["channel_id"=>$caveID, "user_id2"=>$req->user_id2];
        event(new otherChat($datas));

    	return json_encode($datas);

    }


    public function getMessages(Request $req){

        $messages = DB::table("conversation_reply")

        ->where("c_id_fk", $req->convo_id)

        ->join("users","conversation_reply.cr_user_id","=","users.id")

        ->select(
            "users.id",
            "users.name",
            "users.email",

            "conversation_reply.reply",
            "conversation_reply.c_id_fk"
        )

        ->orderBy("cr_id", "ASC")->get();


        return json_encode($messages);

    }


    public function sendMessage(Request $req){

    	DB::table("conversation_reply")->insert([

    		"c_id_fk"    =>  $req->channel_id,
    		"cr_user_id" =>  Auth::user()->id,
    		"reply"      =>  $req->channel_message

    	]);

    	$datas = [ 
    		"channel_id" => $req->channel_id,
    		"message"    => $req->channel_message,
    		"user_data"  => Auth::user()
        ];

        // return $datas['channel_id'];
        //event
    	broadcast(new sendMessage($datas))->toOthers();

    }

}
