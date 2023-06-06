<?php
/**
 * 
 * Apoyar
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Apoyar
 * @package    Apoyar_ProductRules
 * @copyright  Copyright (c) 2023 Apoyar (http://www.apoyar.eu/)
 */

namespace Apoyar\ProductRules\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Logger 
{
    /**
     * @var Context
     */
    protected $context;
    protected $logViewFileName;
    protected $timezone;
    protected $file;
    protected $date;
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param DateTime $date
     * @param Timezone $timezone
     * @param File $file
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        Context $context,
        DateTime $date,
        Timezone $timezone,
        File $file,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->context = $context;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->file = $file;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * create log file for different rules
     * @param $storeId
     * @param $jobCode
     * @return string
     * @throws \Exception
     */
    public function syncLogFile($storeId, $jobCode, $logFileId = null)
    {
        $logPath = $this->getLogPath();
        $time = time();

        if ($logFileId > 0) {
            $time = $logFileId;
        }
        $logFileName = $storeId."_".$jobCode."_" . $time . ".log";
        $filePath  = BP.$logPath.$jobCode."/".$this->date->date('Y-m-d');
        $this->file->checkAndCreateFolder($filePath);

        $htacessFile = BP.$logPath.$jobCode."/.htaccess";
        if (!file_exists($htacessFile)) {
            $content = 'Order allow,deny' . "\n" . "Allow from all" . "\n";
            file_put_contents($htacessFile, $content . "\n");
        }

        $logViewFileName = BP.$logPath.$jobCode."/".$this->date->date('Y-m-d') ."/" . $logFileName;
        if (!($this->file->fileExists($logViewFileName))) {
            umask(002);
            $fp = fopen($logViewFileName, 'a');
        }
        return  $logViewFileName;
    }

    /**
     * Get Log file path from configuration
     * @return string
     */
    public function getLogPath()
    {
        /**
         * Path will take from common configuration if available
         * If not available then it takes static "/var/log/" from root magento folder
         */
        $path = $this->scopeConfig->getValue('productruletab/serverdetails/logPath', ScopeInterface::SCOPE_STORE);
        if ($path == '') {
            return $path = "/var/log/";
        } else {
            $path = rtrim($path, '/') . '/';
            return '/'.ltrim($path, '/');
        }
    }

    /**
     * log invalid rule in the log file
     * @param mixed $logViewFileName
     * @param int $ruleId
     * @param mixed $ruleName
     * @return void
     */
    public function logInvalidRule($logViewFileName, $ruleId, $ruleName)
    {
        $txt = "RULE {$ruleId} ===> {$ruleName} HAS NO PROPER ATTRIBUTE CONDIITIONS";
        $this->writeLogToFile($logViewFileName, $txt);
    }

    /**
     * update the contents of log file
     * @param $logViewFileName
     * @param $txt
     */
    public function writeLogToFile($logViewFileName, $txt)
    {
        $txt = $this->timezone->date(time())->format('Y-m-d H:i:s')." : ".$txt;
        @file_put_contents($logViewFileName, $txt . PHP_EOL, FILE_APPEND);
    }
}
