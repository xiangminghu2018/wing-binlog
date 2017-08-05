<?php namespace Wing\Library;
/**
 * DispatchWorker.php
 * User: huangxiaoan
 * Created: 2017/8/4 12:25
 * Email: huangxiaoan@xunlei.com
 */
class DispatchWorker extends BaseWorker
{
	private $index;

    public function __construct($workers, $index)
	{
		$this->workers = $workers;
		$this->index   = $index;
        for ($i = 1; $i <= $workers; $i++) {
            $this->task[$i] = 0;
        }
	}

    protected function scandir($callback)
    {
        $path[] = HOME."/cache/pos/dispatch_process_".$this->index.'/*';
        while (count($path) != 0) {
            $v = array_shift($path);
            foreach(glob($v) as $item) {
                if (is_file($item)) {
                    $temp = explode("/", $item);
                    $file = array_pop($temp);
                    list($start, $end) = explode("_", $file);
                    $callback($start, $end);
                    unlink($item);
                }
            }
        }
    }

	/**
	 * dispatch process
	 *
	 * @param int $i
	 */
	public function start()
	{
		$i = $this->index;
		$process_id = pcntl_fork();

		if ($process_id < 0) {
			echo "fork a process fail\r\n";
			exit;
		}

		if ($process_id > 0) {
			return $process_id;
		}

		$process_name = "wing php >> dispatch process - ".$i;

		//设置进程标题 mac 会有warning 直接忽略
		set_process_title($process_name);


		$pdo = new PDO();
		$bin = new \Wing\Library\BinLog($pdo);

		while (1) {
			//clearstatcache();
			ob_start();

			try {

				pcntl_signal_dispatch();
                $this->scandir(function($start_pos, $end_pos) use($bin){
                    do {

                        if (!$end_pos) {
                            break;
                        }

                        $worker     = $this->getWorker("parse_process");
                        $cache_path = $bin->getSessions($worker, $start_pos, $end_pos);

                        //echo "生成缓存文件",$cache_path,"\r\n";

                        if (!file_exists($cache_path)) {
                            echo "文件不存在\r\n";
                        }

                    } while (0);
                });

			} catch (\Exception $e) {
				var_dump($e->getMessage());
				unset($e);
			}

			$output = ob_get_contents();
			ob_end_clean();

			if ($output) {
				echo $output, "\r\n";
			}
			unset($output);
			usleep(self::USLEEP);
		}
		return 0;
	}

}