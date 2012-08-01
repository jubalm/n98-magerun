<?php

namespace N98\Magento\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

abstract class AbstractMagentoCommand extends Command
{
    /**
     * @var string
     */
    protected $_magentoRootFolder;

    /**
     * @return string
     */
    public function getMagentoRootFolder()
    {

    }

    /**
     * Search for magento root folder
     *
     * @param OutputInterface $output
     * @param int $recursionLevel
     */
    public function detectMagento(OutputInterface $output)
    {
        if (stristr(PHP_OS, 'win')) {
            $folder = exec('@echo %cd%'); // @TODO not currently tested!!!
        } else {
            $folder = exec('pwd');
        }

        $folders = array();
        $folderParts = explode(DIRECTORY_SEPARATOR, $folder);
        foreach ($folderParts as $key => $part) {
            $explodedFolder = implode(DIRECTORY_SEPARATOR, array_slice($folderParts, 0, $key + 1));
            if ($explodedFolder !== '') {
                $folders[] = $explodedFolder;
            }
        }

        foreach (array_reverse($folders) as $searchFolder) {
            $finder = new Finder();
            $finder
                ->directories()
                ->depth(0)
                ->followLinks()
                ->name('app')
                ->name('skin')
                ->in($searchFolder);

            if ($finder->count() > 0) {
                $files = iterator_to_array($finder, false); /* @var $file \SplFileInfo */
                $this->_magentoRootFolder = dirname($files[0]->getRealPath());
                $output->writeln('<info>Found magento in folder "' . $this->_magentoRootFolder . '"</info>');
                return;
            }
        }

        $output->writeln('<error>Magento folder could not be detected</error>');
    }
}