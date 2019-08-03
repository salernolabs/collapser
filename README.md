# salernolabs/collapser

[![Latest Stable Version](https://poser.pugx.org/salernolabs/collapser/v/stable)](https://packagist.org/packages/salernolabs/collapser)
[![License](https://poser.pugx.org/salernolabs/collapser/license)](https://packagist.org/packages/salernolabs/collapser)
[![Build Status](https://travis-ci.com/salernolabs/collapser.svg?branch=master)](https://travis-ci.org/salernolabs/collapser)

A PHP media collapser/minifier with CSS and JS extensions. Not really re-inventing the wheel this code was written years ago in my proprietary Chorizo platform. Just moving it out into it's own library. I am fully aware that many developers hate inheritance and protected members/methods. For those offended, I apologize, that's just how this was built.

## Usage

Include this project with composer:

    composer require salernolabs/collapser

### CSS

You can create an instance of the collapser you need, default media (useless), CSS, or Javascript.

    $collapser = new \SalernoLabs\Collapser\CSS();
    $collapser->setDeleteComments(true);

    $output = $collapser->collapse($input);

If the input CSS is:

    .helloCSS {
        display: none;
    }

    #somecss {
        color: #ffffff;
        background: url('/images/whatever.gif');
    }

The value of $output would be:

    .helloCSS{display:none;}

### Javascript

    $collapser = new \SalernoLabs\Collapser\Javascript();
    $collapser->setDeleteComments(true);

    $output = $collapser->collapse($input);

If the input Javascript is:

    /**
     Javascript test
     */
    var x = 13;

    function test(i, j, x)
    {
        var output = i + j + x;

        return output;
    }

    //Run the function in the alert
    var detail = test(1, 2, 3);
    alert(detail);

The value of $output should be:

    var x=13;function test(i, j, x){var output=i+j+x;return output;}var detail=test(1, 2, 3);alert(detail);

Note that it doesn't remove spaces for parameters of functions.
