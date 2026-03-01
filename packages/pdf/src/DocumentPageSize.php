<?php
namespace CentralBooking\PDF;

enum DocumentPageSize: string
{
    case A0 = 'a0';
    case A1 = 'a1';
    case A2 = 'a2';
    case A4 = 'a4';
    case A5 = 'a5';
    case A6 = 'a6';
    case A7 = 'a7';
    case A8 = 'a8';
    case A9 = 'a9';
    case A10 = 'a10';
    case B0 = 'b0';
    case B1 = 'b1';
    case B2 = 'b2';
    case B4 = 'b4';
    case B5 = 'b5';
    case B6 = 'b6';
    case B7 = 'b7';
    case B8 = 'b8';
    case B9 = 'b9';
    case B10 = 'b10';
    case C0 = 'c0';
    case C1 = 'c1';
    case C2 = 'c2';
    case C4 = 'c4';
    case C5 = 'c5';
    case C6 = 'c6';
    case C7 = 'c7';
    case C8 = 'c8';
    case C9 = 'c9';
    case C10 = 'c10';
    case RA0 = 'ra0';
    case RA1 = 'ra1';
    case RA2 = 'ra2';
    case RA3 = 'ra3';
    case RA4 = 'ra4';
    case SRA0 = 'sra0';
    case SRA1 = 'sra1';
    case SRA2 = 'sra2';
    case SRA3 = 'sra3';
    case SRA4 = 'sra4';
    case LETTER = 'letter';
    case HALF_LETTER = 'half-letter';
    case LEGAL = 'legal';
    case LEDGER = 'ledger';
    case TABLOID = 'tabloid';
    case EXECUTIVE = 'executive';
    case FOLIO = 'folio';
}