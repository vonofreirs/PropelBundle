<?php
/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Command;

use App\AppBundle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Moritz Schroeder <moritz.schroeder@molabs.de>
 */
trait BundleTrait
{
    /**
     * @return ContainerInterface
     */
    abstract protected function getContainer(): ContainerInterface;

    /**
     * Returns the selected bundle.
     * If no bundle argument is set, the user will get ask for it.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return BundleInterface
     */
    protected function getBundle(InputInterface $input, OutputInterface $output): BundleInterface
    {
        /** @var KernelInterface $kernel */
        $kernel = $this
            ->getContainer()
            ->get('kernel');

        if ($input->hasArgument('bundle') && !empty($input->getArgument('bundle')) && '@' === substr($input->getArgument('bundle'), 0, 1)) {
            return $kernel->getBundle(substr($input->getArgument('bundle'), 1));
        }

        $bundles = $kernel->getBundles();
        $bundles[AppBundle::NAME] = new AppBundle($this->getContainer());

        $bundleNames = array_keys($bundles);

        do {
            $question = '<info>Select the bundle</info>: ';
            $question = new Question($question);
            $question->setAutocompleterValues($bundleNames);

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $bundleName = $questionHelper->ask($input, $output, $question);

            // old bundle structure and new one
            if (in_array($bundleName, $bundleNames) || empty(trim($bundleName)) || $bundleName == AppBundle::NAME) {
                break;
            }
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</bg>', $bundleName));
        } while (true);

        if (empty($bundleName) || $bundleName === AppBundle::NAME) {
            return $bundles[AppBundle::NAME];
        }

        return $kernel->getBundle($bundleName);
    }
}