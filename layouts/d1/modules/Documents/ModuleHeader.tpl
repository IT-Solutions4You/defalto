{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if $FIELDS_INFO neq null}
		<script type="text/javascript">
			var uimeta = (function() {
				var fieldInfo  = {$FIELDS_INFO};
				return {
					field: {
						get: function(name, property) {
							if(name && property === undefined) {
								return fieldInfo[name];
							}
							if(name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						}
					}
				};
			})();
		</script>
	{/if}
{/strip}
