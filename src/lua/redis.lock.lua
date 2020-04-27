local keysOkCount = 0;
local operationOk = nil;
local clientId = ARGV[1];
local expired = ARGV[2];
local tmpClientId = nil;
local lockNamesLen = 2;
for i, v in ipairs(KEYS) do
    operationOk  = redis.call('set', KEYS[i], clientId, 'PX', expired, 'NX');
    if (operationOk) then keysOkCount = keysOkCount + 1 end;
end;

if (keysOkCount == lockNamesLen) then
    return keysOkCount;
end;

for i, v in ipairs(KEYS) do
    tmpClientId  = redis.call('get', KEYS[i]);
    if (tmpClientId == clientId) then
        redis.call('del', KEYS[i]);
    end;
end
return 0;
