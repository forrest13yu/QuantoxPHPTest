<?php
use function \FluidXml\fluidxml;
require_once 'CVSExport.php';
abstract class Handler
{
	protected $m_successor;
	public function setSuccessor($successor)
	{
		$this->m_successor = $successor;
	}

	public abstract function handleRequest($request, $countryes);
}

class JSONHandler extends Handler
{
	public function handleRequest($request, $countryes)
	{
		if ($request === 'json' || $request == '')
		{
			return json_encode($countryes);
		}
		else
		{
			$this->m_successor->handleRequest($request, $countryes);
		}
	}
}

class CSVHandler extends Handler
{
	public function handleRequest($request, $countryes)
	{
		if ($request == 'csv')
		{
			return CVSExport::toCSV($countryes);
		}
		else
		{
			$this->m_successor->handleRequest($request, $countryes);
		}
	}
}

class XMLHandler extends Handler
{
	public function handleRequest($request, $countryes)
	{
		if ($request == 'xml')
		{
			$book = fluidxml();
			$book->add($countryes);
			return $book;
		}
		else
		{
			$this->m_successor->handleRequest($request, $countryes);
		}
	}
}
