<?php


namespace Extensions\Polymatica;

/**
 * Class Api
 * @package Extensions\Polymatica
 */
class Api
{
    use Config;

    /**
     * @var string
     */
    private string $protocol;
    /**
     * @var string
     */
    private string $url;

    public array $requestPull = [];
    public array $responsePull = [];

    /**
     * @const string
     */
    const zerouuid = '00000000-00000000-00000000-00000000';

    /**
     * @var string
     */
    private string $session = '';
    /**
     * @var string
     */
    private string $uuid = self::zerouuid;
    /**
     * @var string
     */
    private string $manager_uuid = self::zerouuid;

    /**
     * @var array
     */
    private array $users = [];
    /**
     * @var string
     */
    private string $loginNow = '';

    /**
     * @var null
     */
    private $layerid = null;
    /**
     * @var array
     */
    private array $cubes = [];
    /**
     * @var array
     */
    private array $cube = [];
    /**
     * @var string
     */
    private string $module_id = self::zerouuid;
    /**
     * @var array
     */
    private array $module = [];

    /**
     * @var array
     */
    private array $dimensions = [];
    /**
     * @var array
     */
    private array $facts = [];
    /**
     * @var array
     */
    private array $dimensions_id = [];
    /**
     * @var array
     */
    private array $dimensions_values = [];
    /**
     * @var array
     */
    private array $facts_id = [];
    /**
     * @var array
     */
    private array $calcFacts = [];

    /**
     * @var array
     */
    private array $scripts = [];

    /**
     * @var array
     */
    private array $script = [];

    /**
     * @var array
     */
    private array $rawData = [];
    /**
     * @var array
     */
    private array $Data = [];

    /**
     * Api конструктор.
     * @param mixed $host
     */
    public function __construct()
    {
        $this->protocol = self::config()->ssl ? 'https' : 'http';
        $this->url = $this->protocol . '://' . self::config()->host . ':' . self::config()->port;
        define('SERVERCODES', json_decode(file_get_contents("{$this->url}/server-codes.json"), true));
    }

    /***
     * Служебные методы
     */

    /**
     * Получение серверных кодов
     *
     * @return array
     */
    public function getCodes(): array
    {
        return SERVERCODES;
    }

    /**
     * Отправка запроса в Полиматику
     *
     * @param mixed ...$queries
     * @return array
     * @throws \Exception
     */
    private function run(...$queries): array
    {
        $preparedParams = [];
        foreach ($queries as $query_id => $query) {
            $preparedParams[$query_id] = [
                'module' => $query['module'] ?? 'manager',
                'command' => $query['command'],
                'state' => $query['state'],
                'params' => $query['params'] ?? null,
            ];
        }

        $query = $this->prepare($this->prepareQuery($preparedParams));

        $this->requestPull[] = $query;
        if ($this->protocol === 'https') {
            $response = \Http::withoutVerifying()->post($this->url, $query);
        } else {
            $response = \Http::post($this->url, $query);
        }

        $responseBody = json_decode($response->body(), true);

        $this->responsePull[] = $responseBody;

        if ($responseBody['error']['code'] !== 0) throw new \Exception($responseBody['error']['message'], $responseBody['error']['code']);

        return array_map(fn($q) => $q['command'], $responseBody['queries']);
    }

    /**
     * Подготовка команд
     *
     * @param array $queries
     * @return array
     */
    private function prepareQuery(array $queries): array
    {
        $prepare = [];
        foreach ($queries as $query_id => $query) {
            $module = SERVERCODES[$query['module']];
            $command = $module['command'][$query['command']];
            $code = $command['id'];
            $state = $command['state'][$query['state']];

            $prepare[$query_id] = [
                'uuid' => $this->uuid,
                'command' => [
                    'plm_type_code' => $code,
                    'state' => $state
                ]
            ];

            if ($query['params'] !== null) {
                foreach ($query['params'] as $cmdKey => $Cmd) {
                    $prepare[$query_id]['command'][$cmdKey] = $Cmd;
                }
            }
        }
        return $prepare;
    }

    /**
     * Подготовка запроса
     *
     * @param array $queries
     * @return array
     */
    private function prepare(array $queries): array
    {
        return [
            'state' => 0,
            'session' => $this->session,
            'queries' => $queries
        ];
    }

    /**
     * Получение рабочей области
     *
     * @return $this
     * @throws \Exception
     */
    public function render(): Api
    {
        $this->rawData = $this->run([
            'module' => 'olap',
            'command' => 'view',
            'state' => 'get',
            'params' => [
                'from_row' => 0,
                'from_col' => 0,
                'num_row' => $this->getCube()['row_count'] ?? 100,
                'num_col' => $this->getCube()['row_count'] ?? 100
            ]
        ]);

        return $this;
    }


    /***
     * Геттеры и Сеттеры
     */


    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getManagerUuid(): string
    {
        return $this->manager_uuid;
    }

    /**
     * @param string $manager_uuid
     */
    public function setManagerUuid(string $manager_uuid): void
    {
        $this->manager_uuid = $manager_uuid;
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return null|string
     */
    public function getLayerid()
    {
        return $this->layerid;
    }

    /**
     * @param string $layerid
     */
    public function setLayerid(string $layerid): void
    {
        $this->layerid = $layerid;
    }

    /**
     * @return array
     */
    public function getCubes(): array
    {
        return $this->cubes;
    }

    /**
     * @param array $cubes
     */
    public function setCubes(array $cubes): void
    {
        $this->cubes = $cubes;
    }

    /**
     * @return mixed
     */
    public function getCube(): array
    {
        return $this->cube;
    }

    /**
     * @param mixed $cube
     */
    public function setCube(array $cube): void
    {
        $this->cube = $cube;
    }

    public function setActiveCube(string $activeCube, bool $byName = false): Api
    {
        if($byName) {
            $this->setCube(current(
                array_filter($this->getCubes(), fn($cube) => $cube['name'] === $activeCube)
            ));
        } else {
            $this->setCube(current(
                array_filter($this->getCubes(), fn($cube) => $cube['uuid'] === $activeCube)
            ));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @param array $scripts
     */
    public function setScripts(array $scripts): void
    {
        $this->scripts = $scripts;
    }

    /**
     * @return array
     */
    public function getScript(): array
    {
        return $this->script;
    }

    /**
     * @param array $scripts
     */
    public function setScript(array $script): void
    {
        $this->script = $script;
    }

    /**
     * @return array
     */
    public function getDims(): array
    {
        return $this->dimensions;
    }

    /**
     * @param array $dimensions
     */
    public function setDims(array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return array
     */
    public function getFacts(): array
    {
        return $this->facts;
    }

    /**
     * @param array $facts
     */
    public function setFacts(array $facts): void
    {
        $this->facts = $facts;
    }

    /**
     * @return array
     */
    public function getDimensionsId(): array
    {
        return $this->dimensions_id;
    }

    /**
     * @param array $dimensions_id
     */
    public function setDimensionsId(array $dimensions_id): void
    {
        $this->dimensions_id = $dimensions_id;
    }

    /**
     * @return array
     */
    public function getDimensionsValues(): array
    {
        return $this->dimensions_values;
    }

    /**
     * @return array
     */
    public function getFactsId(): array
    {
        return $this->facts_id;
    }

    /**
     * @param array $facts_id
     */
    public function setFactsId(array $facts_id): void
    {
        $this->facts_id = $facts_id;
    }

    /**
     * @return array
     */
    public function getModule(): array
    {
        return $this->module;
    }

    /**
     * @param array $module
     */
    public function setModule(array $module): void
    {
        $this->module = $module;
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * @return array
     */
    public function getCalcFacts(): array
    {
        return $this->calcFacts;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->Data;
    }

    /**
     * @return string
     */
    public function getLoginNow(): string
    {
        return $this->loginNow;
    }

    /**
     * @param string $loginNow
     */
    public function setLoginNow(string $loginNow): Api
    {
        $this->loginNow = $loginNow;

        return $this;
    }


    /***
     * Методы работы с Пользователями и Авторизацией
     */


    /**
     * @param string|null $login
     * @param string|null $password
     * @param bool $force
     * @return $this
     * @throws \Exception
     */
    public function authenticate(string $login = null, string $password = null, bool $force = false): Api
    {
        if ($login === null && $password === null) {
            $login = self::config()->login;
            $password = self::config()->password;
        }

        if (!$this->checkAuth() || $force === true) {
            $response = current($this->run([
                'command' => 'authenticate',
                'state' => 'login',
                'params' => [
                    'login' => $login,
                    'passwd' => $password,
                    'locale' => SERVERCODES['locale']['ru']
                ]
            ]));
            $this->session = $response['session_id'];
            $this->setManagerUuid($response['manager_uuid']);
        }

        return $this;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    private function checkAuth(): bool
    {
        $uuid = $this->getUuid();

        $this->uuid = $this->getManagerUuid();
        $response = current($this->run([
            'command' => 'authenticate',
            'state' => 'check'
        ]));

        $this->setUuid($uuid);

        if ($response['session_id'] === '') return false;

        return true;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function logout(): Api
    {
        $this->setUuid($this->getManagerUuid());
        $response = current($this->run([
            'command' => 'user',
            'state' => 'logout'
        ]));

        return $this;
    }

    /**
     * @param string $login
     * @return $this
     * @throws \Exception
     */
    public function deleteUser(string $login): Api
    {
        $this->setUuid($this->getManagerUuid());
        $response = current($this->run([
            'command' => 'user',
            'state' => 'list_request'
        ]));

        if (!isset($response['users'])) throw new \Exception(json_encode($response), 502);
        $this->users = $response['users'];

        $user = current(array_filter($this->users, fn($filterUser) => $filterUser['login'] === $login));

        $response = $this->run([
            'command' => 'user',
            'state' => 'del_user',
            'params' => [
                'user_id' => $user['uuid']
            ]
        ]);

        return $this;
    }


    /***
     * Методы работы со Слоями
     */


    /**
     * @return $this
     * @throws \Exception
     */
    public function createLayer(): Api
    {
        $this->setUuid($this->getManagerUuid());

        $response = $this->run([
            'command' => 'user_layer',
            'state' => 'create_layer'
        ]);

        $layer = current($response)['layer'];
        $this->setLayerid($layer['uuid']);
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function closeLayer(): Api
    {
        $this->setUuid($this->getManagerUuid());

        $response = $this->run([
            'command' => 'user_layer',
            'state' => 'close_layer',
            'params' => [
                'layer_id' => $this->getLayerid()
            ]
        ]);

        if (!isset(current($response)['error'])) $this->setLayerid(null);
        return $this;
    }


    /***
     * Методы работы с Кубами
     */

    public function Cubes(): Api
    {
        $this->setUuid($this->getManagerUuid());
        $response = $this->run([
            'command' => 'user_cube',
            'state' => 'list_request'
        ]);
        $this->setCubes(current($response)['cubes']);

        return $this;
    }

    /**
     * @param string|null $cubeId
     * @param bool $byName
     * @return $this
     * @throws \Exception
     */
    public function Cube(string $cubeId = null, bool $byName = false): Api
    {
        if ($cubeId === null) return $this;

        $this->setActiveCube($cubeId, $byName);
        return $this;
    }

    /**
     * @param string|null $layerId
     * @return $this
     * @throws \Exception
     */
    public function openCube(string $layerId = null): Api
    {
        $this->setUuid($this->getManagerUuid());

        $response = $this->run([
            'command' => 'user_cube',
            'state' => 'open_request',
            'params' => [
                'cube_id' => $this->getCube()['uuid'],
                'layer_id' => $layerId ?? $this->getLayerid(),
                'module_id' => self::zerouuid,
            ]
        ]);

        $this->setModule(current($response)['module_desc']);
        $this->module_id = $this->getModule()['uuid'];

        return $this;
    }


    /***
     * Методы работы с Размерностями
     */


    /**
     * @return $this
     * @throws \Exception
     */
    public function Dims(): Api
    {
        $this->setUuid($this->module_id);

        $response = $this->run([
            'module' => 'olap',
            'command' => 'dimension',
            'state' => 'list_rq'
        ]);
        $this->setDims(current($response)['dimensions']);
        $this->setDimensionsId(array_map(fn($dimension) => $dimension['id'], $this->getDims()));

        return $this;
    }

    /**
     * @param string $dimensionId
     * @return $this
     * @throws \Exception
     */
    public function getDimValues(string $dimensionId): Api
    {
        $this->setUuid($this->module_id);

        $this->moveDim($dimensionId, 'left', 0)
            ->toggleFilterMode()
            ->render()
            ->closeLayer();

        $response = array_filter(current($this->rawData)['left'], fn($row) => current($row)['type'] === 2);

        $this->dimensions_values = array_map(fn($row) => current($row)['value'], $response);

        return $this;
    }

    /**
     * @param string $factName
     * @return string
     */
    public function DimNameToId(string $dimName): string
    {
        $dimension = current(
            array_filter($this->getDims(), fn($filterDim) => $filterDim['name'] === $dimName)
        );
        return $dimension['id'] ?? '';
    }
    /**
     * @param string $dimensionId
     * @param string $position
     * @param int $level
     * @return $this
     * @throws \Exception
     */
    public function moveDim(string $dimensionId, string $position, int $level = 0): Api
    {
        $response = $this->run([
            'module' => 'olap',
            'command' => 'dimension',
            'state' => 'move',
            'params' => [
                'position' => $position === 'top' ? 2 : 1,
                'id' => $dimensionId,
                'level' => $level
            ]
        ]);

        return $this;
    }

    public function foldAtLevel(string $position = 'left', int $level = 0): Api
    {
        $response = $this->run([
            'module' => 'olap',
            'command' => 'view',
            'state' => 'fold_all_at_level',
            'params' => [
                'position' => $position === 'top' ? 2 : 1,
                'level' => $level
            ]
        ]);

        return $this;
    }


    /***
     * Методы работы с Фактами
     */


    /**
     * @return $this
     * @throws \Exception
     */
    public function Facts(): Api
    {
        $this->setUuid($this->module_id);

        $response = $this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'list_rq'
        ]);
        $this->setFacts(current($response)['facts']);
        $this->setFactsId(array_map(fn($fact) => $fact['id'], $this->facts));

        return $this;
    }

    /**
     * @param string $template
     * @return string
     */
    public function FactFormulaNameToId(string $template): string
    {
        $facts = $this->getFacts();
        $result = preg_replace_callback('/fact\([а-яА-Яa-zA-Z0-9- ]*\)/um',
            function ($item) use ($facts) {
                $factName = current($item);
                $fact = current(array_filter($facts, fn($filterFact) => 'fact(' . $filterFact['name'] . ')' === $factName));
                return 'fact(' . $fact['id'] . ')';
            }, $template);

        return $result;
    }

    /**
     * @param string $factName
     * @return string
     */
    public function FactNameToId(string $factName): string
    {
        $fact = current(
            array_filter($this->getFacts(), fn($filterFact) => $filterFact['name'] === $factName)
        );
        return $fact['id'] ?? '';
    }

    /**
     * @param string $name
     * @param string $formula
     * @return $this
     * @throws \Exception
     */
    public function createCalcFact(string $name, string $formula): Api
    {
        $formula = $this->FactFormulaNameToId($formula);

        $this->setUuid($this->module_id);
        $response = $this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'create_calc',
            'params' => [
                'name' => $name,
                'formula' => $formula,
                'uformula' => $formula
            ]
        ]);

        $this->calcFacts[] = [
            'name' => $name,
            'id' => current($response)['create_id'],
            'formula' => $formula
        ];

        return $this;
    }

    /**
     * @param string $factName
     * @param string $name
     * @param array|null $typeparam
     * @return $this
     * @throws \Exception
     */
    public function createCopyFact(string $factName, string $name, array $typeparam = null): Api
    {
        $factId = $this->FactNameToId($factName);

        $this->setUuid($this->module_id);

        $response = current($this->run(
            [
                'module' => 'olap',
                'command' => 'fact',
                'state' => 'create_copy',
                'params' => [
                    'fact' => $factId
                ]
            ]
        ));
        if (isset($response['error'])) return $this;

        $factId = $response['create_id'];

        if ($typeparam !== null) {
            $response = current($this->run(
                [
                    'module' => 'olap',
                    'command' => 'fact',
                    'state' => 'set_type',
                    'params' => [
                        'fact' => $factId,
                        'type' => $typeparam['type']
                    ]
                ]
            ));

            $level = $typeparam['level'] ?? null;
            if ($level !== null) $this->setFactLevel($factId, $level);

            if (isset($response['error'])) return $this;
        }

        $response = current($this->run(
            [
                'module' => 'olap',
                'command' => 'fact',
                'state' => 'rename',
                'params' => [
                    'fact' => $factId,
                    'name' => $name
                ]
            ]
        ));
        if (isset($response['error'])) return $this;

        return $this;
    }

    /**
     * @param string $factId
     * @param int $level
     * @return $this
     * @throws \Exception
     */
    public function setFactLevel(string $factId, int $level = 1): Api
    {
        $response = current($this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'set_level',
            'params' => [
                'fact' => $factId,
                'level' => $level
            ]
        ]));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function selectAllFacts(): Api
    {
        $response = $this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'select_all'
        ]);

        return $this;
    }

    public function setHorizontal(string $factName, bool $isHorizontal): Api
    {
        $factId = $this->FactNameToId($factName);
        $response = $this->run(
            [
                'module' => 'olap',
                'command' => 'fact',
                'state' => 'set_type',
                'params' => [
                    'fact' => $factId,
                    'is_horizontal' => $isHorizontal
                ]
            ]
        );

        return $this;
    }

    /**
     * @param string $factId
     * @param bool $isSelected
     * @return $this
     * @throws \Exception
     */
    public function toggleSelectedFact(string $factId, bool $isSelected): Api
    {
        $response = $this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'set_selection',
            'params' => [
                'fact' => $factId,
                'is_seleceted' => $isSelected
            ]
        ]);
        $this->Facts();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function hideSelectedFacts(): Api
    {
        $response = $this->run([
            'module' => 'olap',
            'command' => 'fact',
            'state' => 'hide_selected'
        ]);

        return $this;
    }


    /***
     * Методы работы с фильтрам
     */


    /**
     * @param string $filterDimId
     * @param array $filter
     * @return $this
     * @throws \Exception
     */
    public function setFilter(string $filterDimId, array $filter): Api
    {
        $filterValues = current($this->run([
            'module' => 'olap',
            'command' => 'filter',
            'state' => 'get_data',
            'params' => [
                'dimension' => $filterDimId,
                'from' => 0,
                'num' => 99999
            ]
        ]));

        $filterData = array_filter($filterValues['data'], fn($value) => in_array((string)$value, $filter));
        $markKeys = array_keys($filterData);
        $marks = [];
        foreach ($filterValues['marks'] as $markId => $mark) {
            if (array_search($markId, $markKeys) === false) {
                $marks[$markId] = 0;
            } else {
                $marks[$markId] = 1;
            }
        }

        $response = $this->run(
            [
                'module' => 'olap',
                'command' => 'filter',
                'state' => 'filter_all_flag',
                'params' => [
                    'dimension' => $filterDimId,
                ]
            ],
            [
                'module' => 'olap',
                'command' => 'filter',
                'state' => 'apply_data',
                'params' => [
                    'dimension' => $filterDimId,
                    'from' => 0,
                    'marks' => array_slice($marks, 0, 500)
                ]
            ],
            [
                'module' => 'olap',
                'command' => 'filter',
                'state' => 'set',
                'params' => [
                    'dimension' => $filterDimId,
                ]
            ]);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function toggleFilterMode(): Api
    {
        $this->setUuid($this->module_id);

        $this->run([
            'module' => 'olap',
            'command' => 'dimension',
            'state' => 'set_filter_mode'
        ]);

        return $this;
    }

    /***
     * Методы по работе со сценариями
     */

    public function scriptsList(): Api
    {
        $this->setUuid($this->getManagerUuid());
        $response = $this->run([
            'module' => 'manager',
            'command' => 'script',
            'state' => 'list'
        ]);

        $this->setScripts(current($response)['script_descs']);

        return $this;
    }

    private function scriptByName(string $name): Api
    {
        $scripts = $this->getScripts();

        $this->setScript(current(array_filter($scripts, fn($script) => $script['name'] === $name)));

        return $this;
    }

    private function scriptById(string $id): Api
    {
        $scripts = $this->getScripts();

        $this->setScript(current(array_filter($scripts, fn($script) => $script['uuid'] === $id)));

        return $this;
    }

    private function scriptCheck(): int
    {
        $this->setUuid($this->getManagerUuid());

        $response = $this->run([
            'module' => 'manager',
            'command' => 'script',
            'state' => 'run_progress',
            'params' => [
                'layer_id' => $this->getLayerid()
            ]
        ]);

        return current($response)['status']['code'];
    }

    private function scriptIsFinish(int $status): bool
    {
        if($status === (int) SERVERCODES['error']['process_finish']) {
            return true;
        }

        return false;
    }

    public function scriptRun(string $script, bool $byName = false): Api
    {
        if($byName) {
            $this->scriptByName($script);
        } else {
            $this->scriptById($script);
        }

        $this->setUuid($this->getManagerUuid());
        $response = $this->run([
            'command' => 'script',
            'state' => 'run',
            'params' => [
                'script_id' => $this->getScript()['uuid']
            ]
        ]);

        $this->setLayerid(current($response)['layer']['uuid']);

        $this->scriptCheck();

        while ($this->scriptIsFinish($this->scriptCheck())) {}

        return $this;
    }
}
