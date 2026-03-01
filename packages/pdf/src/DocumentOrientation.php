<?php
namespace CentralBooking\PDF;

enum DocumentOrientation: string
{
    case LANDSCAPE = 'landscape';
    case PORTRAIT = 'portrait';
}