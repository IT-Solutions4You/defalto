<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Core_Controller_Action implements Core_Controller_Interface
{
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isLoginRequired(): bool
    {
        return true;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateReadAccess();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @throws Exception
     */
    public function getViewer(Vtiger_Request $request)
    {
        throw new Exception ('Action - implement getViewer - JSONViewer');
    }

    /**
     * @param Vtiger_Request $request
     */
    public function preProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * This will return all the permission checks that should be done
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $permissions = $this->requiresPermission($request);

        foreach ($permissions as $permission) {
            if (array_key_exists('module_parameter', $permission)) {
                if ($request->has($permission['module_parameter']) && !empty($request->get($permission['module_parameter']))) {
                    $moduleParameter = $request->get($permission['module_parameter']);
                } elseif ($request->has('record') && !empty($request->get('record'))) {
                    $moduleParameter = getSalesEntityType($request->get('record'));
                }
            } else {
                $moduleParameter = 'module';
            }

            if (array_key_exists('record_parameter', $permission)) {
                $recordParameter = $request->get($permission['record_parameter']);
            } else {
                $recordParameter = '';
            }

            if (!Users_Privileges_Model::isPermitted($moduleParameter, $permission['action'], $recordParameter)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    // Control the exposure of methods to be invoked from client (kind-of RPC)
    protected array $exposedMethods = [];

    /**
     * Function that will expose methods for external access
     *
     * @param string $name - method name
     */
    protected function exposeMethod(string $name): void
    {
        if (!in_array($name, $this->exposedMethods)) {
            $this->exposedMethods[] = $name;
        }
    }

    /**
     * Function checks if the method is exposed for client usage
     *
     * @param string $name - method name
     *
     * @return bool
     */
    public function isMethodExposed(string $name): bool
    {
        if (in_array($name, $this->exposedMethods)) {
            return true;
        }

        return false;
    }

    /**
     * Function invokes exposed methods for this class
     *
     * @param string         $name - method name
     *
     * @throws Exception
     */
    public function invokeExposedMethod(string $name)
    {
        $parameters = func_get_args();
        $name = array_shift($parameters);

        if (!empty($name) && $this->isMethodExposed($name)) {
            return call_user_func_array([$this, $name], $parameters);
        }

        throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
    }
}