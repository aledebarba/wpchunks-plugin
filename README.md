<h1 align="center">WPChunks Wordpress Plugin 👋</h1>
<p>
  <img alt="Version" src="https://img.shields.io/badge/version-1.0.0-blue.svg?cacheSeconds=2592000" />
  <a href="#" target="_blank">
    <img alt="License: ISC" src="https://img.shields.io/badge/License-ISC-yellow.svg" />
  </a>
  <a href="https://twitter.com/alemacedo" target="_blank">
    <img alt="Twitter: alemacedo" src="https://img.shields.io/twitter/follow/alemacedo.svg?style=social" />
  </a>
</p>

## About the project

WPChunks is a Wordpress Plugin / Module to include a basic component system in Wordpress Themes.

WPChunks use a **CLI** tool to create plugins templates and insert the generated code via PHP function or shortcode. 

### 🏠 [Homepage](https://github.com/aledebarba/wpchunks-plugin)

### Use:

Insert components via code:

    chunk('<component-name>'[ , <arguments> ]);   

    where <arguments> are values, in the same way we pass arguments to functions. 

Insert components via shortcode:

    To be implemented

Create components

1. **install the cli tool**

    npm i -g wpchunks-cli

2. **Use cli tool** from your folder theme (or subfolder inside it like "components") to create the components code:

    wpchunk <component-name> [<component-type>]

Create a folder with the component name and create the component code inside it. 
If you don't specify the component type, the default component is a PHP components.

    <component-type>

    one of the options:

    php - creates a php component
    javascript - creates a vanilla javascript component
    js - short for javascript
    react - creates a react component
    vue - create a vue component
    react-collection - creates a collection of separated callable react components inside the same folder


    ex: 
    wpchunk hello-world react

    creates an react component named hello-world
    


#### The ReactJS component thing

Wordpress (since version 5?) deliver ReactJs libs embeded inside all js things it sends to client side when the user access a Wordpress page. WPChunks just wire everything necessary to make the code work on client side.

#### The PHP component 

###### Include the component with

    Component::P('component-name');

The component also includes it's own stylesheet (if exist in the project). The stylesheet is a **sass** code compiled server side and embeded to the style section of the page that calls the component. If many components use the same scss, the code embeded justo one time.

###### Parameters

    Component::P('component-name', $param1, $param2, $param3, ...);

The component code receives an array of parameters


## Motivation
Besides Wordpress's templating system(s) like templating, code loop, file routing, blocks system, parts system and json templating, from my experience, every approach is a little bit confusing. From a front-end developer perspective, components should embed some sugar to make life easy. 

I started this idea just to accomplish onde goal: include SCSS seamlesly inside my php files. Then I created some functions to control the compiled CSS code and embed it when the code was called.

After that I create a function to take care of js code embeded in the page, and finally this code allowed me to embed react code in the final product.


## Author

👤 **Alexandre CMC Souza**

* Website: http://alemacedo.com
* Twitter: [@alemacedo](https://twitter.com/alemacedo)
* Github: [@aledebarba](https://github.com/aledebarba)
* LinkedIn: [@aledebarba](https://linkedin.com/in/aledebarba)

***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_