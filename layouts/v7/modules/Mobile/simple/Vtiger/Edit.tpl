{*<!--
/*************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Commercial
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
**************************************************************************************/
-->*}
{include file="../Header.tpl" scripts=$_scripts}
<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/Vtiger/js/Edit.js"></script>
{literal}

    <form name="editForm" id="field-edit-form" ng-submit="saveThisRecord()" ng-controller="VtigerEditController">
        <header md-page-header fixed-top>
            <md-toolbar>
                <div class="md-toolbar-tools actionbar">
                    <md-button ng-click="gobacktoUrl()" class="md-icon-button" aria-label="side-menu-open">
                        <i class="mdi mdi-window-close actionbar-icon"></i>
                    </md-button>
                    <h2 ng-if="record" flex>Edit</h2>
                    <h2 ng-if="!record" flex>Create</h2>
                    <span flex></span>
                    <md-button type="submit" class="md-icon-button" aria-label="notifications">
                        <i class="mdi mdi-check actionbar-icon"></i>
                    </md-button>
                </div>
            </md-toolbar>
        </header>
        <section layout="row" flex class="content-section">
            <div layout="column" class="edit-content" layout-fill layout-align="top center" ng-if="fieldsData.length">
                <md-list class="fields-list">
                    <md-list-item class="md-1-line" ng-repeat="field in fieldsData" ng-if="field.editable">
                        <div class="md-list-item-text field-row" ng-switch="field.fieldType"> 
                            <!--*************Picklist**************************************-->
                            <md-input-container ng-switch-when="picklist">
                                <label class="edit-select-label">{{field.label}}</label>
                                <md-select ng-model="field.value" name="{{field.name}}" aria-label="{{field.name}}" ng-required="field.mandatory">
                                    <md-option ng-value="opt.value" ng-repeat="opt in field.picklist">{{opt.label}}</md-option>
                                </md-select>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>
                            <!--*************Picklist**************************************-->
                            <md-input-container ng-switch-when="metricpicklist">
                                <label class="edit-select-label">{{field.label}}</label>
                                <md-select ng-model="field.value" name="{{field.name}}" aria-label="{{field.name}}" ng-required="field.mandatory">
                                    <md-option ng-value="opt.value" ng-repeat="opt in field.picklist">{{opt.label}}</md-option>
                                </md-select>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--****************Multi Select Picklist*******************************-->
                            <md-input-container ng-switch-when="multipicklist">
                                <div style="border: 1px solid;">{{field.label}}</div>
                                <div style="border: 1px solid;">{{field.valuelabel}}</div><br>
                                <div style="border: 1px solid;">{{field.value}}</div><br>
                                <div style="border: 1px solid;">{{field.name}}</div><br>
                                <div style="border: 1px solid;">{{field.fieldType}}</div><br>
                                <div style="border: 1px solid;">{{field.fieldFormat}}</div><br>
                                <div style="border: 1px solid;">{{field.picklist}}</div><br>
                                <label>{{field.label}}</label>
                                <md-chips ng-model="field.valuelabel" md-autocomplete-snap md-require-match>
                                    <md-autocomplete aria-label="{{field.name}}"
                                                     md-input-name="field.name"
                                                     md-search-text="field.valuelabel"
                                                     md-items="item in querySearch2(field.picklist)"
                                                     md-item-text="item">
                                        <span md-highlight-text="fruitsobj.searchText">{{item.display}}</span>
                                    </md-autocomplete>
                                    <md-chip-template>
                                        <span> {{$chip['display']}} </span>
                                    </md-chip-template>
                                </md-chips>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--****************Reference Picklist*******************************-->
                            <div ng-switch-when="reference">
                                <label style="font-size: 13px; color: grey;">{{field.label}}</label>
                                <md-autocomplete flex
                                                 ng-required="field.mandatory"
                                                 ng-model="field.value"
                                                 aria-label="field.name"
                                                 md-input-name="field.name"
                                                 md-search-text="field.valuelabel"
                                                 md-items="item in getMatchedReferenceFields(field.valuelabel)"
                                                 md-item-text="item.label"
                                                 md-min-length="2"
                                                 md-selected-item-change="field.value = item.id; field.valuelabel = item.label"
                                                 placeholder="Type to search">
                                    <md-item-template>
                                        <span md-highlight-text="searchText">{{item.label}}</span>
                                    </md-item-template>
                                    <md-not-found>
                                        No matches found for "{{field.valuelabel}}".
                                    </md-not-found>
                                </md-autocomplete>
                                <div ng-messages="editForm.{{field.name}}.$error" ng-if="searchForm.autocompleteField.$touched">
                                    <div ng-message="required">You <b>must</b> have a favorite fruit.</div>
                                </div>
                            </div>
                            <!--*************Phone number************************************-->
                            <md-input-container ng-switch-when="phone">
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.value" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Skype number************************************-->
                            <md-input-container ng-switch-when="skype" class="skype-field">
                                <label>{{field.label}}</label>
                                <i class='mdi mdi-skype skype-icon'></i>
                                <input class="skype-input" name="{{field.name}}" ng-model="field.value" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Textbox UI ************************************-->
                            <md-input-container ng-switch-when="string">
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.value" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value" ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Date Field UI***********************************-->
                            <md-input-container class="date-input-container" ng-switch-when="date" style="width: 100%;">
                                <label>{{field.label}}</label>
                                <md-datepicker
                                    class="edit-date-picker"
                                    name = "{{field.name}}"
                                    ng-model="field.dateFieldValue"
                                    value="field.value"
                                    ng-change="setdateString(field)"
                                    ng-aria-label="field.name"
                                    >
                                </md-datepicker>
                                <!--datepicker date-format="{{field.fieldFormat | ngdateformat}}">
                                    <input class="date-input" name="field.name" ng-model="field.value" type="text" ng-required="field.mandatory">
                                </datepicker-->
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--****************Time UI***********************************-->
                            <md-input-container  class="date-input-container" ng-switch-when="time">
                                <label>{{field.label}}</label>
                                <clockpicker ng-model="field.value" time12format="field.valuelabel" time24format="field.value" appliedname="field.name" aria-label="{{field.name}}" ng-required="field.mandatory"></clockpicker>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Date & Time UI***********************************-->
                            <md-input-container ng-switch-when="datetime">
                                <div >This is datetime</div>
                            </md-input-container>

                            <!--*************Text Area UI***********************************-->
                            <md-input-container ng-switch-when="text">
                                <label>{{field.label}}</label>
                                <textarea ng-model="field.value" columns="1" name="{{field.name}}" aria-label="{{field.name}}" ng-required="field.mandatory"></textarea>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Double UI***********************************-->
                            <md-input-container ng-switch-when="double">
                                <label>{{field.label}}</label>
                                <input type="text" name="{{field.name}}" ng-model="field.value" pattern="[0-9]+([\.|,][0-9]+)?" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                    <div ng-show="editForm.field.name.$error.text" ng-message="ngInvalidPattern"> match failed</div>
                                </div>
                            </md-input-container>

                            <!--*************Integer UI***********************************-->
                            <md-input-container ng-switch-when="integer">
                                <label>{{field.label}}</label>
                                <input type="text" name="{{field.name}}" ng-model="field.value" pattern="[0-9]+" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Currency UI***********************************-->
                            <md-input-container ng-switch-when="currency">
                                <label>{{field.label}}</label>
                                <input type="text" name="{{field.name}}" ng-model="field.value" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Email UI***********************************-->
                            <md-input-container ng-switch-when="email" novalidate>
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.value" type="email" aria-label="{{field.name}}" ng-required="field.mandatory" novalidate>

                                <div ng-messages="editForm.field.name.$error">
                                    <div ng-show="field.mandatory" ng-message="required"> Mandatory Field.</div>
                                    <div ng-show="editForm.{{field.name}}.$error.email" ng-message="email">
                                        Not valid email!
                                    </div>
                                </div>
                            </md-input-container>

                            <!--*************Url UI***********************************-->
                            <md-input-container ng-switch-when="url" novalidate>
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.value" type="url" aria-label="{{field.name}}" ng-required="field.mandatory" novalidate>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Owner UI***********************************-->
                            <md-input-container ng-switch-when="owner">
                                {{field}}
                                <label class="edit-select-label">{{field.label}}</label>
                                <md-select ng-model="field.value" name="{{field.name}}" aria-label="{{field.name}}" ng-required="field.mandatory">
                                    <md-optgroup label="Users" aria-label="Users">
                                        <md-option ng-value="user" ng-repeat="user in field.picklist.users" aria-label="{{field.name}}">{{user}}</md-option>
                                        {{user}}
                                    </md-optgroup>
                                    <md-optgroup label="Groups">
                                        <md-option ng-value="group" ng-repeat="group in field.picklist.groups" aria-label="{{field.name}}">{{group}}</md-option>
                                    </md-optgroup>
                                </md-select>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Checkbox /Boolean Box UI *********************-->
                            <md-input-container ng-switch-when="boolean">
                                <!--label class="edit-checkbox-label">{{field.label}}</label-->
                                <md-checkbox class="md-primary edit-checkbox" name="{{field.name}}" ng-model="field.value" aria-label="{{field.name}}"  ng-required="field.mandatory">
                                    {{field.label}}
                                </md-checkbox>
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value"  ng-message="required"> Mandatory Field.</div>
                                </div>
                            </md-input-container>

                            <!--*************Image UI***********************************-->
                            <md-input-container ng-switch-when="image">
                                <div class="mdi mdi-image"></div>
                            </md-input-container>
                            
                            <md-input-container ng-switch-when="richtext">
                                <label>{{field.label}}</label>
                                <textarea rows="4" ng-model="field.value"></textarea>
                            </md-input-container>

                            <!--*************Auto generated UI***********************************-->
                            <md-input-container ng-switch-when="autogenerated">
                                <div>This is autogenerated</div>
                            </md-input-container>

                            <!--*************Default text to be changed Later**********************-->
                            <md-input-container ng-switch-default>
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.value" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                                <div ng-messages="editForm.{{field.name}}.$error">
                                    <div ng-show="field.mandatory && !field.value" ng-message="required"> Mandatory Field.</div>
                                </div>                                
                            </md-input-container>

                        </div>
                    </md-list-item>
                </md-list>
            </div>
            <div class="no-records-message" ng-if="!fieldsData.length">
                <div class="no-records">No Fields Found</div>
            </div>
            <div flex></div>
        </section>
    </form>
{/literal}
