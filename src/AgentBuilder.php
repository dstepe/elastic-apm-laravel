<?php

namespace AG\ElasticApmLaravel;

use AG\ElasticApmLaravel\Collectors\EventDataCollector;
use AG\ElasticApmLaravel\Exception\MissingAppConfigurationException;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Nipwaayoni\AgentBuilder as NipwaayoniAgentBuilder;
use Nipwaayoni\ApmAgent;
use Nipwaayoni\Config;
use Nipwaayoni\Contexts\ContextCollection;
use Nipwaayoni\Events\EventFactoryInterface;
use Nipwaayoni\Middleware\Connector;
use Nipwaayoni\Stores\TransactionsStore;

class AgentBuilder extends NipwaayoniAgentBuilder
{
    /** @var Repository */
    private $app_config;

    /** @var Collection */
    private $collectors;

    public function withAppConfig(Repository $app_config): self
    {
        $this->app_config = $app_config;

        return $this;
    }

    public function withEventCollectors(Collection $collectors): self
    {
        $this->collectors = $collectors;

        return $this;
    }

    protected function newAgent(
        Config $config,
        ContextCollection $sharedContext,
        Connector $connector,
        EventFactoryInterface $eventFactory,
        TransactionsStore $transactionsStore): ApmAgent
    {
        if (null === $this->app_config) {
            throw new MissingAppConfigurationException();
        }

        if (null === $this->collectors) {
            $this->collectors = new Collection();
        }

        $agent = new Agent($config, $sharedContext, $connector, $eventFactory, $transactionsStore, $this->app_config);

        $this->collectors->each(function (EventDataCollector $collector) use ($agent) {
            $agent->addCollector($collector);
        });

        return $agent;
    }
}
