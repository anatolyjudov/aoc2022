<?php

declare(strict_types = 1);

const INPUT_FILENAME = 'input.txt';
const QUALITY_MINUTES = 24;

class RobotsEnum
{
    const OR = 'OR';
    const CL = 'CL';
    const OB = 'OB';
    const GE = 'GE';

    public static function all(): array
    {
        return [self::OR, self::CL, self::OB, self::GE];
    }
}

class ResourcesEnum
{
    const OR = 'or';
    const CL = 'cl';
    const OB = 'ob';
    const GE = 'ge';

    public static function all(): array
    {
        return [self::OR, self::CL, self::OB, self::GE];
    }
}

class Blueprints
{
    const REGEXP = '/Blueprint\s(\d+)+\D+(\d+)\sore\D+(\d+)\sore\D+(\d+)\sore\D+(\d+)\sclay\D+(\d+)\sore\D+(\d+)\sobsidian/';

    public array $all = [];

    public function read(): self
    {
        foreach (explode(PHP_EOL, file_get_contents(INPUT_FILENAME)) as $b) {
            if (!preg_match(self::REGEXP, $b, $matches)) {
                die('Line isn\'t recognized:' . $b);
            }
            $id = (int) $matches[1];
            $this->all[$id] = new Blueprint(
                $id,
                [
                    RobotsEnum::OR => (new Cost())
                        ->set(ResourcesEnum::OR, $matches[2])
                    ,
                    RobotsEnum::CL => (new Cost())
                        ->set(ResourcesEnum::OR, $matches[3])
                    ,
                    RobotsEnum::OB => (new Cost())
                        ->set(ResourcesEnum::OR, $matches[4])
                        ->set(ResourcesEnum::CL, $matches[5])
                    ,
                    RobotsEnum::GE => (new Cost())
                        ->set(ResourcesEnum::OR, $matches[6])
                        ->set(ResourcesEnum::OB, $matches[7])
                    ,
                ]
            );
        }
        return $this;
    }

    public function all(): array
    {
        return $this->all;
    }
}

class Blueprint
{
    /**
     * @var Cost[]
     */
    public array $costs = [];

    public array $maxRobots = [];

    public int $id;

    public function __construct(int $id, array $costs)
    {
        $this->id = $id;

        foreach (RobotsEnum::all() as $r) {
            $this->costs[$r] = array_shift($costs);
        }

        foreach ($this->costs as $cost) {
            foreach(ResourcesEnum::all() as $res) {
                $robot = StrategyHelper::getRobotForResource($res);
                if ($res === ResourcesEnum::GE) {
                    $this->maxRobots[$robot] = 999999;
                    continue;
                }
                if (empty($this->maxRobots[$robot]) || ($this->maxRobots[$robot] < $cost->get($res))) {
                    $this->maxRobots[$robot] = $cost->get($res);
                }
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }
}

class Cost
{
    public array $resourcesCost;

    public function __construct()
    {
        foreach (ResourcesEnum::all() as $r) {
            $this->resourcesCost[$r] = 0;
        }
    }

    public function set($r, $cost): self
    {
        $this->resourcesCost[$r] = (int) $cost;
        return $this;
    }

    public function get($r): int
    {
        return $this->resourcesCost[$r];
    }
}

class StrategyHelper
{
    /*
     * returns minutes to wait to have resources for the robot
     * returns null if it's impossible
     */
    public static function minutesToHaveResources($robot, &$state, Blueprint $blueprint): ?int
    {
        $robotCost = $blueprint->costs[$robot];

        $maxMinutes = 0;
        foreach (ResourcesEnum::all() as $res) {
            if (
                ($robotCost->get($res) === 0)
                || ($state['resources'][$res] >= $robotCost->get($res))
            ) {
                $resMinutes = 0;
            } elseif ($state['robots'][self::getRobotForResource($res)] === 0) {
                $maxMinutes = null;
                break;
            } else {
                $resMinutes = (int) ceil(
                    ($robotCost->get($res) - $state['resources'][$res])
                    / $state['robots'][self::getRobotForResource($res)]
                );
            }
            if ($maxMinutes < $resMinutes) {
                $maxMinutes = $resMinutes;
            }
        }

        return $maxMinutes;
    }

    public static function getRobotForResource($res): string
    {
        switch ($res) {
            case ResourcesEnum::OR: return RobotsEnum::OR;
            case ResourcesEnum::CL: return RobotsEnum::CL;
            case ResourcesEnum::OB: return RobotsEnum::OB;
            case ResourcesEnum::GE: return RobotsEnum::GE;
            default: die();
        }
    }
}

class QualityCalculator
{
    protected int $minutesLimit;
    private int $maxForBlueprint;
    protected array $minutesStates;

    public function __construct(int $minutesLimit)
    {
        $this->minutesLimit = $minutesLimit;
    }

    public function count(Blueprint $blueprint): int
    {
        p('---');
        p('Blueprint ' . $blueprint->getId());

        $geodes = $this->getMaxPossibleGeodes($blueprint);

        p('Best geodes', $geodes);
        $qualityLevel = $geodes * $blueprint->getId();
        p('Quality: ' . $qualityLevel);
        return $qualityLevel;
    }

    protected function getMaxPossibleGeodes(Blueprint $blueprint): int
    {
        $state = [
            'minutesSpent' => 0,
            'robots' => [
                RobotsEnum::OR => 1,
                RobotsEnum::CL => 0,
                RobotsEnum::OB => 0,
                RobotsEnum::GE => 0,
            ],
            'resources' => [
                ResourcesEnum::OR => 0,
                ResourcesEnum::CL => 0,
                ResourcesEnum::OB => 0,
                ResourcesEnum::GE => 0,
            ],
            'strategy' => ''
        ];

        $this->maxForBlueprint = 0;
        $this->minutesStates = [];
        $this->nextStep($state, $blueprint);
        $bestGeodes = $this->maxForBlueprint;

        return $bestGeodes;
    }

    protected function nextStep(array $state, Blueprint $blueprint): ?array
    {
        if ($state['minutesSpent'] >= $this->minutesLimit) {
            // check for the result and return
            if ($state['resources'][ResourcesEnum::GE] > $this->maxForBlueprint) {
                $this->maxForBlueprint = $state['resources'][ResourcesEnum::GE];
                p($state['minutesSpent'], 'Found strategy that gives', $this->maxForBlueprint, 'geodes');
                p(chunk_split($state['strategy'], 10, ' '));
                return $state;
            }
            //p('not the best strategy');
            return null;
        }

        // possible scenarios
        {
            // which robot we can build with these resources
            $strategies = [];
            foreach(RobotsEnum::all() as $robot) {
                $minutesToWait = StrategyHelper::minutesToHaveResources($robot, $state, $blueprint);
                if (
                    ($minutesToWait !== null)                        // impossible to build
                    && (($state['minutesSpent'] + $minutesToWait + 1) < $this->minutesLimit) // too long to wait
                    && (($blueprint->maxRobots[$robot] > $state['robots'][$robot])) // too many robots
                ) {
                    $strategies[$robot] = $minutesToWait;
                }
            }
            // empty strategy (means that nothing could be build, and we need to wait to the end
            if (empty($strategies)) {
                $strategies['wait'] = $this->minutesLimit - $state['minutesSpent'];
            }
        }

        // looping over strategies
        $bestResult = 0;
        $bestResultState = null;
        foreach ($strategies as $robot => $minutesToWait) {

            $newState = $state;

            // store strategy
            $newState['strategy'] .= str_repeat('.', $minutesToWait);
            if ($robot !== 'wait') {
                $minutesToBuild = 1;
            } else {
                $minutesToBuild = 0;
            }

            // collecting phase
            foreach (ResourcesEnum::all() as $resource) {
                $newState['resources'][$resource] +=
                    ($minutesToWait + $minutesToBuild)
                    * $newState['robots'][StrategyHelper::getRobotForResource($resource)];
            }

            if ($robot !== 'wait') {
                // spending phase
                foreach (ResourcesEnum::all() as $resource) {
                    $newState['resources'][$resource] -= $blueprint->costs[$robot]->get($resource);
                }
                // manufacturing phase
                $newState['robots'][$robot]++;
                $newState['strategy'] .= $robot[1];
            }

            // add minutes
            $newState['minutesSpent'] += $minutesToWait + $minutesToBuild;

            //
            if ($this->isWorseThatWeKnow($newState)) {
                continue;
            }

            $strategyResult = $this->nextStep($newState, $blueprint);
            if ($strategyResult === null) continue;
            if ($strategyResult['resources'][ResourcesEnum::GE] > $bestResult) {
                $bestResult = $strategyResult['resources'][ResourcesEnum::GE];
                $bestResultState = $newState;
            }
        }

        return $bestResultState;
    }

    protected function saveNewMinuteState($minute, array &$state)
    {
        $this->minutesStates[$minute] = $state;
        p('New best state for minute', $minute);

        $i = 1;
        $newState = $state;
        while(!isset($this->minutesStates[$minute + $i]) && (($minute + $i) <= $this->minutesLimit)) {
            foreach (ResourcesEnum::all() as $res) {
                $newState['resources'][$res] += $newState['robots'][StrategyHelper::getRobotForResource($res)];
            }
            $this->minutesStates[$minute + $i] = $state;
            p('New best state for minute', $minute + $i);
            $i++;
        }
    }

    protected function isWorseThatWeKnow(array &$state): bool
    {
        $minute = $state['minutesSpent'];
        if (!isset($this->minutesStates[$minute])) {
            $this->saveNewMinuteState($minute, $state);
            return false;
        }

        $minutesLeft = $this->minutesLimit - $state['minutesSpent'];
        $stateIsBetter = true;
        $storedIsBetter = true;
        foreach (ResourcesEnum::all() as $res) {
            $stateValue = $state['resources'][$res]
                + $state['robots'][StrategyHelper::getRobotForResource($res)] * $minutesLeft;
            $storedValue = $this->minutesStates[$minute]['resources'][$res]
                + $this->minutesStates[$minute]['robots'][StrategyHelper::getRobotForResource($res)] * $minutesLeft;
            if ($stateValue <= $storedValue) {
                $stateIsBetter = false;
            }
            if ($stateValue > $storedValue) {
                $storedIsBetter = false;
            }
        }
        if ($storedIsBetter) {
            return true;
        }
        if ($stateIsBetter) {
            $this->saveNewMinuteState($minute, $state);
            return false;
        }

        return false;
    }
}

$blueprints        = (new Blueprints())->read();
$qualityCalculator = new QualityCalculator(QUALITY_MINUTES);
$qualityLevels     = [];
$sumQuality        = 0;

foreach($blueprints->all() as $n => $blueprint) {
    $qualityLevels[$n] = $qualityCalculator->count($blueprint);
    $sumQuality += $qualityLevels[$n];
}

printf('----%sFirst star: %d%s', PHP_EOL, $sumQuality, PHP_EOL);

function p(...$args)
{
    foreach ($args as $arg) {
        if (is_array($arg) || is_object($arg)) {
            print_r($arg);
            continue;
        }
        echo $arg . ' ';
    }
    echo PHP_EOL;
}