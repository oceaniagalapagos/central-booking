<?php
namespace CentralBooking\Data;

final class Time
{
	private int $totalSeconds;

	public function __construct(string $hhmmss = '00:00:00')
	{
		$hhmmss = preg_match('/^\s*$/', $hhmmss) ? '00:00:00' : trim($hhmmss);

		if (!preg_match('/^\d+:\d+(?::\d+)?$/', $hhmmss)) {
			throw new \InvalidArgumentException(
				"Formato inválido. Solo se permiten dígitos y ':' con hasta dos ':'. " .
				"Ejemplos válidos: 0:0, 00:00, 0:00, 0:0:0"
			);
		}

		if (substr_count($hhmmss, ':') === 1) {
			$hhmmss .= ':0';
		}

		$this->totalSeconds = self::parseToSeconds($hhmmss);
	}

	public static function fromSeconds(int $seconds): self
	{
		$obj = new self('00:00:00');
		$obj->totalSeconds = $seconds;
		return $obj;
	}

	public function toSeconds(): int
	{
		return $this->totalSeconds;
	}

	public function format(string $format = 'H:i:s')
	{
		return date($format, $this->totalSeconds + 3600 * 24);
	}

	public function add(int $hours = 0, int $minutes = 0, int $seconds = 0): self
	{
		$this->totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
		return $this;
	}

	public function sub(int $hours = 0, int $minutes = 0, int $seconds = 0): self
	{
		return $this->add(-$hours, -$minutes, -$seconds);
	}

	public function addTime(Time $other): self
	{
		$this->totalSeconds += $other->totalSeconds;
		return $this;
	}

	public function subTime(Time $other): self
	{
		$this->totalSeconds -= $other->totalSeconds;
		return $this;
	}

	public function diff(Time $other): Time
	{
		return self::fromSeconds(abs($this->totalSeconds - $other->totalSeconds));
	}

	private static function parseToSeconds(string $hhmmss): int
	{
		$hhmmss = trim($hhmmss);
		$sign = 1;
		if ($hhmmss !== '' && $hhmmss[0] === '-') {
			$sign = -1;
			$hhmmss = substr($hhmmss, 1);
		}
		$parts = explode(':', $hhmmss);
		if (count($parts) !== 3) {
			throw new \InvalidArgumentException('Formato inválido, se espera HH:MM:SS');
		}
		[$h, $m, $s] = $parts;
		if ($h === '' || $m === '' || $s === '') {
			throw new \InvalidArgumentException('Formato inválido, valores vacíos en HH:MM:SS');
		}
		if (!ctype_digit($h) || !ctype_digit($m) || !ctype_digit($s)) {
			throw new \InvalidArgumentException('HH, MM y SS deben ser números enteros no negativos');
		}
		$hours = (int) $h; // puede ser >= 24
		$minutes = (int) $m;
		$seconds = (int) $s;
		if ($minutes < 0 || $minutes > 59) {
			throw new \InvalidArgumentException('MM debe estar entre 0 y 59');
		}
		if ($seconds < 0 || $seconds > 59) {
			throw new \InvalidArgumentException('SS debe estar entre 0 y 59');
		}
		return $sign * (($hours * 3600) + ($minutes * 60) + $seconds);
	}

	public function pretty()
	{
		return $this->format('H:i a');
	}
}