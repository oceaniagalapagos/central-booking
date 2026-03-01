<?php
namespace CentralBooking\PDF;

interface DocumentInterface
{
    public function getHeaderHtml(): string;
    public function getBodyHtml(): string;
    public function getPageSize(): DocumentPageSize;
    public function getOrientation(): DocumentOrientation;
}