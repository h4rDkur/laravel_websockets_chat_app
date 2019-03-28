@extends('layouts.app')

@section('content')

<style type="text/css" media="screen">

[ng\:cloak], [ng-cloak], .ng-cloak {
  display: none !important;
}
 
</style>

<div class="container" ng-app="rootApp" ng-controller="rootController">
  <div class="row justify-content-center" ng-cloak>
    <div class="col-md-12">
      <table class="table table-dark table-striped ">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col" >status</th>
            <th scope="col" style="width:60px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr ng-repeat="user in users track by $index">
            <th scope="row"><# $index+1 #></th>
            <td><# user.email #></td>
            <td><# user.name  #></td>
            <td><button class="bnt btn-warning btn-sm" ng-click="new_convo(user.id)">Message</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          
          <div class="row">
            <div class="col-md-3">
              
              <ul class="list-group">

                <li ng-repeat="contact in contacts_list" class="list-group-item d-flex justify-content-between align-items-center" ng-click="get_messages(contact.c_id)" style="cursor: pointer">
                  <# contact.user_details[0].email #>
                  <small><# contact.status #></small>
                  <span class="badge badge-primary badge-pill">0</span>
                </li>

              </ul>


            </div>
            <div class="col-md-9">

              <div class="row">


                <div class="col-md-12">
                  <div style="min-height:200px; max-height:40px; border:1px solid #ccc; border-radius: 5px;  overflow: auto;">

                    <div style="padding:10px" ng-repeat="message in messages">
                      <span class="badge badge-warning">John</span>
                      : <# message.reply #>
                    </div>
                    
                  </div>
                  <!-- list of chat -->
                </div>
                <div class="col-md-12" style="margin-top:20px">
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Message" aria-label="Message" aria-describedby="basic-addon2" ng-model="current_channel.message">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" ng-click="send_message()">Send</button>
                    </div>
                  </div>
                </div>



              </div><!-- row -->

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')

<script src="{{ asset('/js/angular_js.js') }}"></script>

<script type="text/javascript">


//root angular app =====================================================================================
var rootApp = angular.module('rootApp', [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('<#');
    $interpolateProvider.endSymbol('#>');
});

rootApp.controller('rootController', function($scope, $http, $timeout, $compile, $rootScope) { 


//  subscribed channel -------------------------------------------->
// window.Echo.private('private-chat')
// // event   listen -----------------> 
// .listen('client-typing', (e) => {
//     console.log("i heard something \n");
//     console.log(e);
// });

// presence hannel ------------------------------------------------>

 // $http.get('/employer/list_of_posted_jobs').then(function successCallback(response){
 //            $scope.list_of_jobs = response.data;
 //            console.log($scope.list_of_jobs);
 //        });


$scope.my_private_subs_ids = [];

$scope.get_my_private_subs =function(){

    $http.get('/my_private_subs').then(function successCallback(response){

        $scope.my_private_subs_ids = response.data;

        $scope.sub_to_all_my_private_channel();

    });
}

$scope.get_my_private_subs();

$scope.sub_to_all_my_private_channel = function(){

    angular.forEach($scope.my_private_subs_ids, function(data) {

          console.log("subscribting to private channel :" + data);

          Echo.private('private_room'+data)
          // event   listen -----------------> 
          .listen('sendMessage', (e) => {
              console.log("i heard something some subsrcing to many channels \n");
              
              // console.log(e.datas.message); 
              // console.log(e.datas.message);
              // $scope.messages.reply.push(e.datas.message);
          });

    });
}


//'=========================================================================================================================

$scope.current_channel = {};


$scope.new_convo = function(user_id2){

    data = { "user_id2" : user_id2 };

    $http({method: 'POST',url: '/new_convo', data}).then(function successCallback(response) {

        $scope.current_channel = response.data;

        $scope.check_subscription();

        console.log("created new convo");

    }, function errorCallback(response) {
    // called asynchronously if an error occurs
    // or server returns response with an error status.
    });
}


// ===========================================================================================================

$scope.check_subscription_loop = function() {
  return new Promise(resolve => {
   
     for(var c=0; c < $scope.my_private_subs_ids.length; c++){
           console.log("looping + "+c);
           if($scope.my_private_subs_ids[c]==$scope.current_channel.channel_id){

                resolve("already_subscribed");
           
           }
      }  
      resolve("available");
  });
}

$scope.check_subscription = async function() {

    const sub = await $scope.check_subscription_loop();

    console.log(sub);

    if(sub=="available"){
          $scope.my_private_subs_ids.push($scope.current_channel.channel_id);
          
          console.log("im subscribing to new channel");
          Echo.private('private_room'+$scope.current_channel.channel_id)
          // event   listen -----------------> 
          .listen('sendMessage', (e) => {
              console.log("i heard something \n");
              //
              // add this to message list 
              //
              console.log(e);

              console.log("add me to message list");
          });

    }
}

// ===========================================================================================================



$scope.check_subscription_from_others_loop = function() {
  return new Promise(resolve => {
   
     for(var c=0; c < $scope.my_private_subs_ids.length; c++){
           console.log("looping + "+c);
           if($scope.my_private_subs_ids[c]==$scope.others_channel){

                resolve("already_subscribed");
           
           }
      }  
      resolve("available");
  });
}

$scope.check_subscription_from_others = async function() {

    const sub = await $scope.check_subscription_from_others_loop();

    console.log(sub);

    if(sub=="available"){

          $scope.my_private_subs_ids.push($scope.others_channel);
          
          console.log("im subscribing to others channel");
          Echo.private('private_room'+$scope.others_channel)
          // event   listen -----------------> 
          .listen('sendMessage', (e) => {
              console.log("i heard something \n");
              //
              // add this to message list 
              //
              console.log(e);

              console.log("add me to message list");
          });

    }
}

// ===========================================================================================================



$scope.send_message = function(){

    if($scope.current_channel.channel_id!=null && $scope.current_channel.message!=null){  

        data = {
            "channel_id" :      $scope.current_channel.channel_id,
            "channel_message" : $scope.current_channel.message,
        };

        $http({method: 'POST',url: '/send_message', data}).then(function successCallback(response) {

            console.log(response.data);

        }, function errorCallback(response) {
        // called asynchronously if an error occurs
        // or server returns response with an error status.
        });
    }    
}



// ================================================================================================

$scope.listen_for_other_noise = function(){

    Echo.channel('public_noise')
    // event   listen -----------------> 
    .listen('otherChat', (e) => {
        console.log("i heard something fomr others \n");
        console.log(e);

        //check if my id match the others id
        if('{{Auth::user()->id}}' == e.datas['user_id2'] ){


            $scope.others_channel =  e.datas['channel_id'];

            $scope.check_subscription_from_others();

        }
    });
}

$scope.listen_for_other_noise();


//============================================================================================================


// subsribe to my own PRESENCE TO LET OTHER USER KNOW MY STATUS
window.Echo.join(`status_presence`+'{{Auth::user()->id}}')
.here((users) => {
   console.log("my status presence")
})
.joining((user) => {
   console.log("im joining");
})
.leaving((user) => {
   console.log("img leaving");
});



// FOREACH CONTACTS SUBSCRIBE USING THEIR IDS.

$scope.get_contact_list =function(){

    $http.get('/my_subscriptions').then(function successCallback(response){

        $scope.contacts_list = response.data;

        angular.forEach($scope.contacts_list, function(data, key) {

              data.status = "";

              console.log(data.user_details[0].id)
              // presence channel ---------------------------------------------->
              window.Echo.join(`status_presence`+data.user_details[0].id)
              .here((users) => {

                 console.log(users);
                  //if users in this presence is greater than 1 then status is online
                  if(users.length>1){
                      console.log(users[1].email);
                      // timeout fix some var reference thats not working.
                      $timeout(function(){
                            $scope.contacts_list[key].status = "online";
                      })
                  }

              })
              .joining((user) => {

                   $scope.contacts_list[key].status = "online";

              })
              .leaving((user) => {

                    $scope.contacts_list[key].status = "offline";

              });

        });

    });
}

$scope.get_contact_list();



//=============================================================================================================


$scope.get_messages = function(convo_id){


    $scope.current_channel.channel_id = convo_id;

    console.log("gettings messages" + convo_id);


    data = {
        "convo_id" :  convo_id
    };

    $http({method: 'POST',url: '/messages', data}).then(function successCallback(response) {

        console.log(response.data);
        $scope.messages = response.data;

    }, function errorCallback(response) {
    // called asynchronously if an error occurs
    // or server returns response with an error status.
    });


}


//=============================================================================================================


    // === INIT SUB PUBLIC CHANNEL ======================================================

    // presence channel ---------------------------------------------->
    window.Echo.join(`public_chat`)
    .here((users) => {
      // display all users in this channel --------------------------->
      $timeout(function(){
           $rootScope.users = users;
      },500)

    })
    .joining((user) => {

      // add new user who joined this channel ------------------------>
      $timeout(function(){
        $rootScope.users[$rootScope.users.length] = user;
      },500)

    })
    .leaving((user) => {

          // remove users who signed out in this channel --------------.
          $timeout(function(){
          for(var i=0; i<=$rootScope.users.length; i++){
              if($rootScope.users[i].id == user.id)
                 {
                 $rootScope.users.splice(i, 1);
                 break;
                 }
              }
          },500)

    });


 //==== MY STATUS PRESENCE ====================================================================



    // $rootScope.post_new_comment = function(){
     
    //  console.log("img typing");

    //    let channel = Echo.private('chat');

    //        channel.whisper('typing', {
    //             typing: true
    //         });

    // }

    // Echo.private('chat')
    // .listenForWhisper('typing', (e) => {
    //     console.log(e);
    // });




    });
    
</script>

@endsection