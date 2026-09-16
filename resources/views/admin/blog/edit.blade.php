@extends('layouts.admin')
@section('title', __('panel.blog_edit'))
@section('content')
@include('admin.blog._form', ['post' => $post, 'action' => route('admin.blog.update', $post), 'method' => 'PUT'])
@endsection
