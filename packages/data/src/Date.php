<?php
namespace CentralBooking\Data;

use DateTime;

final class Date
{
	private DateTime $date;

	public function __construct(string $Ymd = 'now')
	{
		$this->date = new DateTime($Ymd);
	}

	public function format(string $format = 'Y-m-d')
	{
		return $this->date->format($format);
	}

	public function addDays(int $days)
	{
		$days = absint($days);
		$this->date->modify("+{$days} days");
	}

	public static function today()
	{
		return new Date();
	}

	public function pretty(bool $short = false)
	{
		$months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
		$months_short = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
		$day = date_format($this->date, 'j');
		$month_num = (int) date_format($this->date, 'n');
		$year = date_format($this->date, 'Y');
		if ($short) {
			return "{$day} {$months_short[$month_num - 1]}, {$year}";
		} else {
			return "{$day} de {$months[$month_num - 1]}, {$year}";
		}
	}
}