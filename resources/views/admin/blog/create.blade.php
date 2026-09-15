@extends('layouts.admin')
@section('title', __('panel.blog_new'))
@section('content')
@include('admin.blog._form', ['post' => $post, 'action' => route('admin.blog.store'), 'method' => 'POST'])
@endsection
