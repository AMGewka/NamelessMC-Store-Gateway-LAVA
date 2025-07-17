{if $LAVA_UPDATE_AVAILABLE}
  <style>
    .lava-update-button {
      background-color:rgb(4, 128, 31);
      color: #fff;
      border: none;
      transition: background-color 0.3s;
    }
    .lava-update-button:hover {
      background-color:rgb(0, 255, 136);
    }
  </style>

  <div class="alert alert-success">
    <p><strong>{$GATEWAY_NEW_UPDATE}</strong></p>
    <div class="mt-2">
      {$GATEWAY_CURRENT_VERSION}<strong>{$LAVA_CURRENT_VERSION}</strong><br>
      {$GATEWAY_LATEST_VERSION}<strong>{$LAVA_LATEST_VERSION}</strong>
    </div>
    <br>
    <a href="https://builtbybit.com/resources/lava-gateway-for-store-module.59463/"
       target="_blank"
       class="btn btn-sm lava-update-button">
      <i class="fa-solid fa-download"></i> {$GATEWAY_DOWNLOAD_UPDATE}
    </a>
  </div>
{/if}

<div class="card shadow-sm mb-3 border-left-danger">
        <div class="card-body">
          <h5 class="mb-3"><i class="fa-solid fa-gear"></i> {$GATEWAY_SETTINGS_TITLE}</h5>
          {$GATEWAY_ALERT_URL} <code>{$PINGBACK_URL}</code></br>
          {$GATEWAY_SUCCESS_URL} <code>{$SUCCESS_URL}</code></br></br>

          <form action="" method="post">
          <div class="form-group">
              <label for="inputshopuuid_key">{$GATEWAY_UUID}</label>
              <input type="text" class="form-control" name="shopuuid_key" id="inputshopuuid_key" value="{$SHOP_ID_VALUE}" placeholder="{$GATEWAY_UUID}">
              <small class="form-text text-muted">{$GATEWAY_UUID_FIELD}</small>
            </div>

            <div class="form-group">
              <label for="inputsecret1_key">{$GATEWAY_SECRET1}</label>
              <input type="text" class="form-control" name="secret1_key" id="inputsecret1_key" value="{$SHOP_API_KEY_VALUE}" placeholder="{$GATEWAY_SECRET1}">
              <small class="form-text text-muted">{$GATEWAY_SECRET1_FIELD}</small>
            </div>

            <div class="form-group">
              <label for="inputsecret2_key">{$GATEWAY_SECRET2}</label>
              <input type="text" class="form-control" name="secret2_key" id="inputsecret2_key" value="{$SHOP_API_KEY_2_VALUE}" placeholder="{$GATEWAY_SECRET2}">
              <small class="form-text text-muted">{$GATEWAY_SECRET2_FIELD}</small>
            </div>

            <div class="form-group">
              <label for="inputdesc">{$GATEWAY_PAY_DESC}</label>
                <input type="text" class="form-control" name="desc" id="inputdesc" value="{$DESCRIPTION_VALUE}" placeholder="{$GATEWAY_PAY_DESC}">
              <small class="form-text text-muted">{$GATEWAY_PAY_DESC_FIELD}</small>
            </div>
          {$GATEWAY_PAY_DESK_PLACEHOLDERS} <br>

            <div class="form-group custom-control custom-switch">
              <input id="inputEnabled" name="enable" type="checkbox" class="custom-control-input"{if $ENABLE_VALUE eq 1} checked{/if} />
              <label class="custom-control-label" for="inputEnabled">{$GATEWAY_ENABLE}</label>
            </div>

            <div class="form-group custom-control custom-switch">
              <input id="inputdebug" name="debug" type="checkbox" class="custom-control-input"{if $DEBUG_MODE_VALUE eq 1} checked{/if} />
              <label class="custom-control-label" for="inputdebug">{$GATEWAY_DEBUG_MODE}</label>
            </div>

            <div class="form-group">
              <input type="hidden" name="token" value="{$TOKEN}">
              <input type="submit" value="{$SUBMIT}" class="btn btn-primary">
            </div>
          </form>
        </div></div>