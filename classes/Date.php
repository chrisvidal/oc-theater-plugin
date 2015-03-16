<?php namespace Abnmt\Theater\Classes;



class Date
{


	public static function getMonths($date)
	{

		$months = array(
			1 => 'Января',
			2 => 'Февраля',
			3 => 'Марта',
			4 => 'Апреля',
			5 => 'Мая',
			6 => 'Июня',
			7 => 'Июля',
			8 => 'Августа',
			9 => 'Сентября',
			10 => 'Октября',
			11 => 'Ноября',
			12 => 'Декабря'
		);

		return $months[date( 'n', strtotime($date))];
	}

	public static function getNormalMonths($date)
	{

		$months = array(
			1 =>  'Январь',
			2 =>  'Февраль',
			3 =>  'Март',
			4 =>  'Апрель',
			5 =>  'Май',
			6 =>  'Июнь',
			7 =>  'Июль',
			8 =>  'Август',
			9 =>  'Сентябрь',
			10 => 'Октябрь',
			11 => 'Ноябрь',
			12 => 'Декабрь'
		);

		return $months[date( 'n', strtotime($date))];
	}

	public static function getWeekday($date)
	{

		$weekdays = array(
			1 => 'Понедельник',
			2 => 'Вторник',
			3 => 'Среда',
			4 => 'Четверг',
			5 => 'Пятница',
			6 => 'Суббота',
			7 => 'Воскресенье',
		);

		return $weekdays[date( 'N', strtotime($date))];
	}

	public static function getWeekdayShort($date)
	{

		$weekdays = array(
			1 => 'пн',
			2 => 'вт',
			3 => 'ср',
			4 => 'чт',
			5 => 'пт',
			6 => 'сб',
			7 => 'вс',
		);

		return $weekdays[date( 'N', strtotime($date))];
	}

}
