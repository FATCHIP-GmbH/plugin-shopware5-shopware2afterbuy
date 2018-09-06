
{extends file='backend/viaeb_shopware2_afterbuy_admin/_base/layout.tpl'}

{block name='backend_admin_afterbuy_title'}
    {s name=label/admin_afterbuy_title}Hier können Sie die Grundeinstellungen des Plugins vornehmen und anpassen{/s}
{/block}

{block name='backend_admin_afterbuy_content'}
    {namespace name="frontend/viaebShopware2Afterbuy/backend_config"}
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-body">
                    {if !empty($result)}
                        {if !$result.success}
                            {foreach $result.messages as $error}
                                <div class="alert alert-danger" role="alert">
                                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>&nbsp;
                                    {$error.message}
                                    {if !empty($error.code)}
                                        <span style="white-space: nowrap;"><em>({$error.code})</em></span>
                                    {/if}
                                </div>
                            {/foreach}
                        {/if}
                    {/if}
                    <form id="pluginConfig" method="post" action="{url action=savePluginConfig}">
                        <h3>
                            {s name="heading/connection_data"}
                                Verbindung zur Schnittstelle
                            {/s}
                        </h3>
                        <hr/>

                        <h4>
                            {s name="fieldlabel/AfterbuyShopInterfaceBaseUrl"}
                                AfterbuyShopInterfaceBaseUrl
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyShopInterfaceBaseUrl"></label>
                            <input name="AfterbuyShopInterfaceBaseUrl"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyShopInterfaceBaseUrl()}"
                                    {/if}
                                   id="AfterbuyShopInterfaceBaseUrl">
                        </div>

                        <h4>
                            {s name="fieldlabel/AfterbuyAbiUrl"}
                                AfterbuyAbiUrl
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyAbiUrl"></label>
                            <input name="AfterbuyAbiUrl"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyAbiUrl()}"
                                    {/if}
                                   id="AfterbuyAbiUrl">
                        </div>

                        <h4>
                            {s name="fieldlabel/AfterbuyPartnerId"}
                                AfterbuyPartnerId
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyPartnerId"></label>
                            <input name="AfterbuyPartnerId"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyPartnerId()}"
                                    {/if}
                                   id="AfterbuyPartnerId">
                        </div>

                        <h4>
                            {s name="fieldlabel/AfterbuyPartnerPassword"}
                                AfterbuyPartnerPassword
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyPartnerPassword"></label>
                            <input name="AfterbuyPartnerPassword"
                                   type="password"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyPartnerPassword()}"
                                    {/if}
                                   id="AfterbuyPartnerPassword">
                        </div>

                        <hr/>

                        <h4>
                            {s name="fieldlabel/AfterbuyUsername"}
                                AfterbuyUsername
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyUsername"></label>
                            <input name="AfterbuyUsername"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyUsername()}"
                                    {/if}
                                   id="AfterbuyUsername">
                        </div>

                        <h4>
                            {s name="fieldlabel/AfterbuyUserPassword"}
                                AfterbuyUserPassword
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="AfterbuyUserPassword"></label>
                            <input name="AfterbuyUserPassword"
                                   type="password"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyUserPassword()}"
                                    {/if}
                                   id="AfterbuyUserPassword">
                        </div>

                        <hr/>
                        <h3>
                            {s name="heading/additional_settings"}
                                Zusätzliche Einstellungen
                            {/s}
                        </h3>
                        <hr/>

                        <h4>
                            {s name="fieldlabel/OrdernumberMapping"}
                                OrdernumberMapping
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="OrdernumberMapping"></label>
                            <select id="OrdernumberMapping" name="OrdernumberMapping" form="pluginConfig">
                                {if !empty($config)}
                                    {foreach $ordernumberMapping as $key => $value}
                                        <option
                                                {if $config->getOrdernumberMapping() === $key}
                                                    selected
                                                {/if}
                                                value="{$key}">
                                            {$value}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>

                        <hr/>

                        <h4>
                            {s name="fieldlabel/Category"}
                                Kategorie
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="Category"></label>
                            <input
                                    id="Category"
                                    name="Category"
                                    type="text"
                                    value="{$config->getCategory()}"
                            />
                            <div>
                                {s name="fieldlabel/Category/description"}
                                    Alle Artikel werden der oben genannten Kategorie zugeordnet.
                                    Wenn die Kategorie nicht existiert,
                                    wird sie direkt unter der Kategorie "Deutsch" angelegt.
                                    Wenn keine Kategorie angegeben, müssen alle importierten Artikel
                                    per Hand im Backend einer Kategorie zugeordnet werden.
                                {/s}
                            </div>
                        </div>

                        <hr/>

                        <h4>
                            {s name="fieldlabel/MissingProductsStrategies"}
                                Fehlende Produkte
                            {/s}
                        </h4>
                        <div class="form-group">
                            <div>{s name="fieldlabel/MissingProductsStrategies/description"}
                                    Wenn in Afterbuy ein Artikel gelöscht wurde, der in Shopware noch existiert,
                                    folgende Strategie anwenden:
                                {/s}</div>
                            <label class="sr-only" for="MissingProducts"></label>
                            <select id="MissingProductsStrategy" name="MissingProductsStrategy" form="pluginConfig">
                                {if !empty($config)}
                                    {foreach $missingProductsStrategies as $key => $value}
                                        <option
                                                {if $config->getMissingProductsStrategy() === $key}
                                                    selected
                                                {/if}
                                                value="{$key}">
                                            {$value}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>

                        <hr/>

                        <h4>
                            {s name="fieldlabel/LogLevel"}
                                LogLevel
                            {/s}
                        </h4>
                        <div class="form-group">
                            <label class="sr-only" for="LogLevel"></label>
                            <input name="LogLevel"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getLogLevel()}"
                                    {/if}
                                   id="LogLevel">
                        </div>

                        <hr/>

                        <button id="savePluginConfig" type="submit" class="btn btn-default pull-right">
                            {s name="fieldlabel/saveButton"}
                                Speichern
                            {/s}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='backend_admin_afterbuy_footer'}{/block}

{block name='backend_admin_afterbuy_script'}
{/block}
