<?php
/**
 * Template Name: Hello Page Template
 *
 * @package BarebonesTimber
 */

$context = Timber::context();

$post = Timber::query_post();
$context['post'] = $post;

Timber::render('hello.twig', $context);