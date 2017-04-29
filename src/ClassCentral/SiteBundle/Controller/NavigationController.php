<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Entity\Offering;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Renders the navigation bar
 */
class NavigationController extends Controller
{
    /**
     * @var string
     */
    private $offeringCountCacheKey = 'navigation_offerings_count';

    /**
     * @var string
     */
    private $initiativeCountCacheKey = 'navigation_initiatives_count';

    /**
     * @var string
     */
    private $streamCountCacheKey = 'navigation_stream_count';

    /**
     * @var string
     */
    private $navEventName = 'navbar-clicks';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $data = $this->getNavigationCounts($this->container);

        // Start the session for every user
        $session = $request->getSession();

        if (!$session->isStarted()) {
            // Start the session if its not already started
            $session->start();
        }

        return $this->render('ClassCentralSiteBundle:Helpers:navbar.html.twig', [
            'offeringCount' => $data['offeringCount'],
            'initiativeCount' => $data['initiativeCount'],
            'page' => $page,
            'offeringTypes' => Offering::$types,
            'initiativeTypes' => Initiative::$types,
            'navEventName' => $this->navEventName,
        ]);
    }

    /**
     * @param $container
     *
     * @return mixed
     */
    public function getNavigationCounts(ContainerInterface $container)
    {
        $cache = $container->get('cache');

        $data = $cache->get('navigation_counts', function (ContainerInterface $container) {
            $esCourses = $container->get('es_courses');
            $counts = $esCourses->getCounts();
            $em = $container->get('doctrine')->getManager();

            $offeringCount = [];
            foreach (array_keys(Offering::$types) as $type) {
                if (isset($counts['sessions'][strtolower($type)])) {
                    $offeringCount[$type] = $counts['sessions'][strtolower($type)];
                } else {
                    $offeringCount[$type] = 0;
                }
            }

            $initiativeCount = [];
            foreach (Initiative::$types as $code => $name) {
                if ($code === 'others') {
                    $initiativeCount[$name]['name'] = 'Others';
                } else {
                    $provider = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy(['code' => $code]);
                    $initiativeCount[$name]['name'] = $provider->getName();
                }

                $initiativeCount[$name]['count'] = $counts['providersNav'][$code];
            }

            return compact('offeringCount', 'initiativeCount');

        }, [$container]);

        return $data;
    }
}