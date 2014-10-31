<?php

namespace Message\User\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class UserSummary extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'user_summary';
		$this->displayName = 'User Summary';
		$this->reportGroup = 'Users';
		$this->_charts = [new TableChart];
		parent::__construct($builderFactory,$trans,$routingGenerator);
	}

	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run());
		$columns = $this->getColumns();

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	public function getColumns()
	{
		$columns = [
			['type' => 'number', 	'name' => "ID",			],
			['type' => 'string',	'name' => "Name",		],
			['type' => 'string',	'name' => "Email",		],
			['type' => 'number',	'name' => "Created",	],
		];

		return json_encode($columns);
	}

	private function _getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();

		$queryBuilder
			->select('user.user_id AS "ID"')
			->select('created_at AS "Created"')
			->select('CONCAT(forename, " ",surname) AS "Name"')
			->select('email AS "Email"')
			->from('user')
			->leftJoin("user_group","user.user_id = user_group.user_id",
				$this->_builderFactory->getQueryBuilder()
					->select('user_id')
					->select('GROUP_CONCAT(group_name) AS "group"')
					->from('user_group')
					->groupBy('user_id')
				)
			->orderBy('user.user_id')
		;

		de($queryBuilder->getQueryString());

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {

			$result[] = [
				$row->ID,
				$row->Name,
				$row->Email,
				[ 'v' => $row->Created, 'f' => date('Y-m-d H:i', $row->Created)],
			];

		}

		return json_encode($result);
	}
}
