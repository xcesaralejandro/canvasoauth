## Introduction

canvasoauth is a package developed for Laravel and integrates the Oauth2 credential flow in its entirety. The package provides everything you need to get started and takes care of storing and renewing user tokens. 

To learn more about Oauth2 focused on canvas, you can visit the official Canvas documentation here:
https://canvas.instructure.com/doc/api/file.oauth.html


## Requirements

php >= 8.0

Laravel >= 8.0


## Installation and configuration

#### 1.- Add the package to your project

````composer require xcesaralejandro/canvasoauth````

#### 2.- Publish the provider

````php artisan vendor:publish --provider=xcesaralejandro\canvasoauth\Providers\CanvasOauthServiceProvider --force````

#### 3.- Run the migrations

````php artisan migrate````

#### 4.- Complete the configuration file

Once the providers have been published, you will have a new file called ````canvasoauth.php```` inside the config folder, there you will have to complete the configuration.

The variables to fill in the configuration are the credentials obtained when adding a new API developer key in canvas. While it is generating the credentials, canvas will ask you for your redirection point, use the following for all fields where you need to put a URL:

````https://YOUR_DOMAIN_HERE/canvas/code_exchange````


Going back to the configuration, at this point it may be a bit obvious what goes into each section, but I'll comment on them anyway.



**VERIFY_SELF_SIGNED_HTTPS =>** If false, it allows you to make the required HTTP requests, ignoring that your certificate is self-signed.


**CANVAS_DOMAIN_URL =>** The url of your Canvas instance, for example ***https://YOUR_INSTITUTION.instructure.com***


**CANVAS_CLIENT_ID =>** client_id generado por canvas tras agregar una nueva clave de desarrollador API.


**CANVAS_CLIENT_SECRET =>** client_secret generado por canvas tras agregar una nueva clave de desarrollador API.



## Use

First of all, note that this package manages the authorization tokens and the retrieval flow for you, in no case does it manage users. When referring to the user_id, we are referring to the one provided by canvas. If your application has its own user management, try to store a dictionary of your local identifier and the canvas identifier obtained in the granting of permissions, otherwise, you will not be able to obtain the tokens correctly.

Once you have configured the tool, the flow starts with a predefined link that is built with the credentials. This can be obtained using the following facade:

````
use xcesaralejandro\canvasoauth\Facades\CanvasOauth;

CanvasOauth::getInitialAuthenticationUrl()
````

You can put the URL inside a link, automatic redirection or wherever you want, depending on the application you want to build. Remember that this is not authentication, rather a flow to get an authorization token. Access tokens can be infinitely regenerated, so ideally you should only ask the user for authorization once and then the package will always return a valid token.


To control the flow, use the controller ````App\Http\Controllers\CanvasOauthController.php````

````
    public function onFinish(AuthenticatedUser $user, Request $request) : mixed {
        return parent::onFinish($user); // you can skip this, only creates debug log :)
        // At this point the oauth flow has finished successfully and the user has granted permissions.
    }

    public function onRejectedPermission(Request $request) : mixed {
        return parent::onRejectedPermission($request); // you can skip this, only creates debug log :)
        // At this point the user has canceled the grant of permissions.
    }

    public function onError(\Exception $exception) : mixed {
        return parent::onError($exception); // you can skip this, only creates debug log
        // Any error that may arise during the oauth flow will be thrown here
    }
````

On the other hand, you can manage the tokens with the ````CanvasToken```` model and in the following way:

Check if a token exists for the user

````
CanvasToken::ExistsForUser(int $user_id) : bool
````

Returns the token for a particular user, it is regenerated behind the scenes in case it is expired, so it will always return a valid token. If the consulted user does not have a token, a 404 will be thrown.

```` 
CanvasToken::GetForUser(int $user_id) : string
````
