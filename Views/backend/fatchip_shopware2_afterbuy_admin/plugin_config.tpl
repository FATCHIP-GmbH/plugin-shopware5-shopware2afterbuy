{extends file='backend/fatchip_shopware2_afterbuy_admin/_base/layout.tpl'}

{block name='backend_admin_afterbuy_title'}
    {s name=label/admin_afterbuy_title}Hier können Sie die Grundeinstellungen des Plugins vornehmen und anpassen{/s}
{/block}

{block name='backend_admin_afterbuy_content'}
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
                        <h5>Verbindung zur Schnittstelle</h5>
                        <hr/>

                        <p>{s name=fieldlabel/AfterbuyShopInterfaceBaseUrl}AfterbuyShopInterfaceBaseUrl{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyShopInterfaceBaseUrl">{s name=fieldlabel/AfterbuyShopInterfaceBaseUrl}AfterbuyShopInterfaceBaseUrl{/s}</label>
                            <input name="AfterbuyShopInterfaceBaseUrl"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyShopInterfaceBaseUrl()}"
                                    {/if}
                                   id="AfterbuyShopInterfaceBaseUrl">
                        </div>

                        <p>{s name=fieldlabel/AfterbuyAbiUrl}AfterbuyAbiUrl{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyAbiUrl">{s name=fieldlabel/AfterbuyAbiUrl}AfterbuyAbiUrl{/s}</label>
                            <input name="AfterbuyAbiUrl"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyAbiUrl()}"
                                    {/if}
                                   id="AfterbuyAbiUrl">
                        </div>

                        <p>{s name=fieldlabel/AfterbuyPartnerId}AfterbuyPartnerId{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyPartnerId">{s name=fieldlabel/AfterbuyPartnerId}AfterbuyPartnerId{/s}</label>
                            <input name="AfterbuyPartnerId"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyPartnerId()}"
                                    {/if}
                                   id="AfterbuyPartnerId">
                        </div>

                        <p>{s name=fieldlabel/AfterbuyPartnerPassword}AfterbuyPartnerPassword{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyPartnerPassword">{s name=fieldlabel/AfterbuyPartnerPassword}AfterbuyPartnerPassword{/s}</label>
                            <input name="AfterbuyPartnerPassword"
                                   type="password"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyPartnerPassword()}"
                                    {/if}
                                   id="AfterbuyPartnerPassword">
                        </div>

                        <hr/>

                        <p>{s name=fieldlabel/AfterbuyUsername}AfterbuyUsername{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyUsername">{s name=fieldlabel/AfterbuyUsername}AfterbuyUsername{/s}</label>
                            <input name="AfterbuyUsername"
                                   type="text"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyUsername()}"
                                    {/if}
                                   id="AfterbuyUsername">
                        </div>

                        <p>{s name=fieldlabel/AfterbuyUserPassword}AfterbuyUserPassword{/s}</p>
                        <div class="form-group">
                            <label class="sr-only"
                                   for="AfterbuyUserPassword">{s name=fieldlabel/AfterbuyUserPassword}AfterbuyUserPassword{/s}</label>
                            <input name="AfterbuyUserPassword"
                                   type="password"
                                   class="form-control field-wide"
                                    {if !empty($config)}
                                        value="{$config->getAfterbuyUserPassword()}"
                                    {/if}
                                   id="AfterbuyUserPassword">
                        </div>

                        <hr/>
                        <h5>{s name=fieldlabel/additionalSettings}Zusätzliche Einstellungen{/s}</h5>
                        <hr/>

                        <p>{s name=fieldlabel/OrdernumberMapping}OrdernumberMapping{/s}</p>
                        <div class="form-group">
                            <select name="OrdernumberMapping" form="pluginConfig">
                                {if !empty($config)}
                                    {foreach $ordernumberMapping as $key => $value}
                                        <option value="{$key}"
                                                {if $config->getOrdernumberMapping() === $key}
                                        selected
                                                {/if}>
                                            {s name=fieldlabel/$key}{$value}{/s}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>

                        <hr/>

                        <p>{s name=fieldlabel/CatNews}Kategorie Neuheiten{/s}</p>
                        <div class="form-group">
                            <label>
                                <input
                                        name="CatNews"
                                        value="pluginConfig"
                                        type="checkbox"
                                        {if $config->getCatNews()}
                                            checked
                                        {/if}
                                />
                                <br>
                                {s name=fieldlabel/CatNews/description}
                                Wenn aktiviert, werden alle Artikel der Kategorie "Neuheiten" zugeordnet.
                                Andernfalls müssen die Kategorien per Hand zuordnet werden.
                                {/s}
                            </label>
                        </div>

                        <hr/>

                        <p>{s name=fieldlabel/LogLevel}LogLevel{/s}</p>
                        <div class="form-group">
                            <label class="sr-only" for="LogLevel">{s name=fieldlabel/LogLevel}LogLevel{/s}</label>
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
                            {s name=fieldlabel/saveButton}Speichern{/s}
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
