<?php

/*
 * How to use the Tale router!
 */






//1. Use it
use Tale\App\Router;










//2. Define some kind of action.
//There are a few possible kinds of actions possible
//For more information on this check out the PHP doc
//to call_user_func

//This would be how you call a class callback.
class Actor
{
    public function act($routeData)
    {

        var_dump($routeData);
    }
}

$actor = new Actor();



$routeHandler = [$actor, 'act'];
//For static callback use the following
$routeHandler = 'Actor::act';
//or
$routeHandler = ['Actor', 'act'];

//remember to use the full FQCN


//Callback can also be a function
function myRouteHandler($routeData)
{

    var_dump($routeData);
}

$routeHandler = 'myRouteHandler';


//or an anonymous function/Closure

$routeHandler = function($routeData) {

    var_dump($routeData);
};










//3. Define a route

//Imagine a route for a MVC system.
//We have controllers and they have actions we want to call.
//The action can receive an ID

//We need to parse /user/edit/5 into the following array
//[ 'controller' => 'user', 'action' => 'edit', 'id' => 5 ]

//A route for that would look like the following
$route = '/:controller/:action/:id';


//The structure is the following:
//     /            = Initiator for controller. It doesn't have to be a /
//     :            = This tells the router that we want to get a value of this part
//     controller   = This is the name of the part. The initiator will automatically be called *Initiator














//2. Create an instance
//You can pass an array of routes optionally