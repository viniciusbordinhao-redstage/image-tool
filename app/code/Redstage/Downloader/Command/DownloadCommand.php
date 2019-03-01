<?php

namespace Redstage\Downloader\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Redstage\Downloader\Model\Csv\CsvParser;
use Redstage\Downloader\Model\Download\Downloader;

class DownloadCommand extends Command
{
    protected static $defaultName = 'app:download';

    private $_downloader;

    public function __construct(Downloader $downloader)
    {
        $this->_downloader = $downloader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Download Images from CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to CSV file')
            ->setHelp('<file> | /path/to/file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $section1 = $output->section();
        $section2 = $output->section();
        $section3 = $output->section();
        $section1->writeln('Reading csv....');
        $file = $input->getArgument('file');
        $csvParser = new CsvParser($file, ",");
        $section1->overwrite('Read csv: Done');
        $section2->writeln('removing duplicates entries from image column...');
        $items = $csvParser->removeDuplicateFromColumn('image');
        $section2->overwrite('duplicates entries were removed...');
        $section3->writeln('starting download...');
        $i = 0;
        $total = count($items);
        foreach($items as $item){
            $response = $this->_downloader->download($item);
            $id = $i + 1;
            $porcentage = $id*100/$total;
            $text = sprintf('Downloaded Item: %d --------- %d %%', $id, $porcentage);
            $section3->overwrite($text);
            $status = "OK";

            $i++;         
        }

        $output->write('Done.');
    }
}