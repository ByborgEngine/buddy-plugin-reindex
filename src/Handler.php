<?php declare(strict_types=1);

/*
  Copyright (c) 2023, Manticore Software LTD (https://manticoresearch.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 2 or any later
  version. You should have received a copy of the GPL license along with this
  program; if you did not, you can find it at http://www.gnu.org/
*/
namespace Manticoresearch\Buddy\Plugin\Reindex;

use Manticoresearch\Buddy\Core\Plugin\BaseHandler;
use Manticoresearch\Buddy\Core\Task\Column;
use Manticoresearch\Buddy\Core\Task\Task;
use Manticoresearch\Buddy\Core\Task\TaskResult;
use RuntimeException;
use parallel\Runtime;
use function exec;
use function join;

final class Handler extends BaseHandler {
	/**
	 * Initialize the executor
	 *
	 * @param Payload $payload
	 * @return void
	 */
	public function __construct(public Payload $payload) {
	}

  /**
	 * Process the request
	 *
	 * @return Task
	 * @throws RuntimeException
	 */
	public function run(Runtime $runtime): Task {
		$taskFn = static function (): TaskResult {
			$output = [];
			$returnCode = 0;
			exec('indexer --all --rotate', $output, $returnCode);
			return TaskResult::withRow([
				'output' => join(' , ', $output),
				'returnCode' => $returnCode
			])->column('output', Column::String)->column('returnCode', Column::Long);
		};

		return Task::createInRuntime(
			$runtime, $taskFn, []
		)->run();
	}

	/**
	 * @return array<string>
	 */
	public function getProps(): array {
		return [];
	}
}
