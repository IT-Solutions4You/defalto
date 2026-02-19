{strip}
    {if !$FIELD_NAME}
        {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    {/if}
    <div class="input-group dropdown w-100 {$FIELD_NAME}ChangeColorContainer">
        <span class="btn btn-outline-secondary" data-bs-toggle="dropdown">
            <i class="fa-solid fa-palette"></i>
        </span>
        <div class="dropdown-menu dropdown-menu-start h-50vh overflow-auto w-100">
            {foreach from=Core_Color_UIType::getColorList() key=COLORS_LABEL item=COLORS}
                <div class="dropdown-header">{vtranslate($COLORS_LABEL, $QUALIFIED_MODULE)}</div>
                {foreach from=$COLORS key=COLOR_LABEL item=COLOR_ID}
                    <a href="#" class="dropdown-item colorOption d-flex {if $COLOR_VALUE eq $COLOR_ID}fw-bold bg-body-secondary{/if}" data-color-id="{$COLOR_ID}" onclick="vtUtils.setColor('.{$FIELD_NAME}ChangeColorContainer', '{$COLOR_ID}')">
                        <span class="w-25 rounded" style="background: {$COLOR_ID};"></span>
                        <span class="text-capitalize ms-2">{vtranslate($COLOR_LABEL, $QUALIFIED_MODULE)}</span>
                    </a>
                {/foreach}
            {/foreach}
        </div>
        <input type="color" class="form-control form-control-color changeColorValue" value="{$COLOR_VALUE}" oninput="$(this).next().val(this.value)">
        <input type="text" class="form-control changeColorValue" name="selectedColor" value="{$COLOR_VALUE}" oninput="$(this).prev().val(this.value)"/>
    </div>
{/strip}