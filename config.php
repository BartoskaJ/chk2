<?php
// Global configuration

date_default_timezone_set('Europe/Prague');

// Database configuration
const DB_HOST = 'localhost';
const DB_NAME = 'checklists';
const DB_USER = 'root';
const DB_PASS = '';

// Base path of app relative to domain root (with leading and trailing slash)
const BASE_PATH = '/';
const BASE_URL = BASE_PATH; // for backward compatibility

// Origin used for generating absolute URLs
define('APP_ORIGIN', 'http://localhost');

// Mail configuration
const MAIL_FROM = 'noreply@example.com';
const MAIL_FROM_NAME = 'Checklist App';
