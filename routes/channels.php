<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });




// presence private channel;
Broadcast::channel('public_chat', function ($user) {
   	 return $user;
});

// private channel 
/*
	channel_id.{dynamic_var} 
	function($user,$dynamiv_var)

*/

Broadcast::channel('private_room{channel_id}', function ($user) {
   	 return true;
});




