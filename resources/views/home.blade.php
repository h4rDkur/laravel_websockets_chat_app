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
              
{{--               <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Cras justo odio
                  <span class="badge badge-primary badge-pill">14</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Dapibus ac facilisis in
                  <span class="badge badge-primary badge-pill">2</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Morbi leo risus
                  <span class="badge badge-primary badge-pill">1</span>
                </li>
              </ul>
 --}}

            </div>
            <div class="col-md-9">

              <div class="row">


                <div class="col-md-12">
                  <div style="min-height:200px; max-height:40px; border:1px solid #ccc; border-radius: 5px;">
                    <div style="padding:10px">
                      <span class="badge badge-warning">John</span>
                      : kqjwek ksjad kjqiwue kjzk jqiweu
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
              console.log(e);
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
              console.log(e);
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
              console.log(e);
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