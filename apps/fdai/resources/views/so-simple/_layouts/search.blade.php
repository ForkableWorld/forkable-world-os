@extends('so-simple._layouts.page')

@yield('content')

<form onsubmit="return false;">
  <input type="input" id="search" class="search-input" placeholder="Enter your search term..." autofocus>
</form>

<div id="results"></div>
