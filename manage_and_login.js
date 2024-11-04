const d = document;
const baseUrl = 'https://smartmanager.wibiocard.com/api'
const channel = 'NFC'
let apiKey
var _reader;
var _cardId;
var _cardType;
let _supportedCards;
let _target = document.getElementById('spinner-wrapper');

async function listReaders() {
    showSpinner()
    try {
        apiKey = await navigator.webcard.apiKey();
        if(!apiKey)
            throw 'ApiKey not found. Please check your web extension configuration';
        d.querySelector("#h_mess").innerHTML = "WibioWebcard Extension loaded";
        _supportedCards = await getSupportedCards();
        let readers = await navigator.webcard.readers();
            d.querySelector("#t_mess").innerHTML = readers.length + " readers detected";
            d.querySelector("#f_mess").innerHTML = "Put your card on the smartcard reader to start working!";
            d.querySelector("#f_mess").classList.replace("text-danger", "text-success");
            d.querySelector("#b_mess").innerHTML = "";
            var ul = document.createElement('ul');
            ul.classList.add('list-group');
            readers.forEach((reader, index) => {
                const li = document.createElement('li');
                if (reader.atr != "" && _supportedCards.find(c => c.Atr.replaceAll('-', '') == reader.atr))
                {
                    li.classList.add('list-group-item', 'list-group-item-success');
                    li.innerHTML = '<i class="bi bi-credit-card text-success"></i> <span>' + reader.name + '</span><span class="float-end text-sm">Card detected</span>';
                    loadInterface(reader);
                }
                else if (reader.atr != "")
                {
                    li.classList.add('list-group-item', 'list-group-item-danger');
                    li.innerHTML = '<i class="bi bi-credit-card text-danger"></i> <span>' + reader.name +  '</span><span class="float-end text-sm">not supported card</span>';
                }
                else
                {
                    li.classList.add('list-group-item', 'list-group-item-secondary');
                    li.innerHTML = '<i class="bi bi-x-octagon text-secondary"></i> <span>' + reader.name +  '</span><span class="float-end text-sm">Empty slot</span>';
                }
                ul.appendChild(li);
            });
            d.querySelector("#b_mess").appendChild(ul);
    } catch(ex){
        d.querySelector("#f_mess").innerHTML = ex;
        d.querySelector("#f_mess").classList.replace("text-success", "text-danger");
    }
    finally {
        hideSpinner()
    }

    navigator.webcard.cardInserted = function (reader) {
        loadInterface(reader);
    }

    navigator.webcard.cardRemoved = function (reader) {
        unloadInterface(reader);
    }
}

async function CmdsExecutor(reader, commandstr){
    try {
        if(!reader)
            throw 'Reader not found';
        let execResult = [];
        let commands = [];
        const commandsFromServer = await Promise
            .allSettled([...commandstr.matchAll(/\[(.*?)\]/gm)]
            .filter(matches => !!matches[1])
            .map(matches => {
                const commandName = matches[1]
                return new Promise((resolve, reject) => {
                    const splittedCommand = commandName.split(' ');
                    let params = null;
                    if(splittedCommand.length > 1){
                        params = splittedCommand.slice(1, splittedCommand.length)
                        .reduce((aggr, curr) => {
                            let keyVal = curr.replaceAll('{', '').replaceAll('}', '').split('=');
                            if(keyVal.length == 2)
                            aggr[keyVal[0]] = keyVal[1];
                            return aggr;
                        }, {})
                    }
                    getCommand(_cardId, splittedCommand[0])
                        .then(c => resolve({
                        ...c,
                        name: commandName,
                        params: params
                    })).catch(err => reject(err))
                });
            }))
        commandsFromServer
            .filter(r => r.status == 'fulfilled')
            .map(r => ({
                name: r.value.name,
                command: r.value.Response,
                params: r.value.params
            }))
            .forEach(r => {
                if(!commands.find(c => c.name == r.name))
                    commands.push(r)
            })

        execResult = execResult.concat(await execOnReader(reader, commands))
        if(execResult && Array.isArray(execResult)){
            return execResult;
        }
    } finally {
        hideSpinner();
    }
}

async function execOnReader(reader, commands) {
    try{
        if (!reader)
            throw 'Card not found on reader';
        await reader.connect(true);
        const results = []
        if(!Array.isArray(commands))
            commands = [commands]
        for(const c of commands){
            let startTime = new Date();
            try {
                const commandResult = await reader.transceive(c.command, c.params);
                results.push({
                    result: commandResult,
                    status: 'ok',
                    name: c.name
                })
            } catch(ex){
                console.log('error during apdu execution', ex)
                results.push({
                    status: 'incomplete',
                    name: c.name
                })
            }
            results[results.length - 1].elapsed = new Date() - startTime;
        }
        return results
    } catch(ex) {
        manageMessages("#b_mess", "d", ex);
    } finally {
        reader.disconnect();
    }
}


//**************************API**************************//

async function verifyTokenByEmail(token) {
    try {
        let email =  d.querySelector("#user_email").innerHTML;
        const response = await fetch(`${baseUrl}/verifyTokenByEmail/${token}/${email}`, {
            method: 'GET',
            headers: {
            'X-Authorization': apiKey
            }
        })
        return await response.text()
    } catch(ex) {
        manageMessages("#b_mess", "d", "error during verification request. Check your internet connection and try again");
    }
}

async function getSupportedCards(renew = false)
{
    if(!renew && _supportedCards && _supportedCards.length){
        return _supportedCards;
    }
    try {
        const response = await fetch(`${baseUrl}/getSupportedCards`, {
            method: 'GET',
            headers: {
                'X-Authorization': apiKey
            }
        });
        const data = await response.json();
        _supportedCards = [...(data.Cards || [])];
        return _supportedCards;
    } catch(ex){
        console.log(ex);
    }
    throw 'No supported cards found. Please check your internet connection and try again';
}

async function checkCardByAtr(Atr){
    try {
        const response = await fetch(`${baseUrl}/checkCardByAtr/${channel}/${Atr}`, {
            method: 'GET',
            headers: {
            'X-Authorization': apiKey
            }
        })
        return await response.json()
    } catch(ex) {
        console.log(ex);
    }
    throw 'Check card by atr failed. Please check your smartcard and try again';
}

async function getCommand(cardId, commandName) {
    if (commandName == "PersonalizeF")
        return getPersonalizationCommand(cardId, commandName, {"token": d.querySelector("#token").value});
    try {
        const response = await fetch(`${baseUrl}/generateCommand/${cardId}/${channel}/${commandName}`, {
            method: 'GET',
            headers: {
            'X-Authorization': apiKey
            }
        })
        return await response.json()
    } catch(ex) {
        console.log(ex);
    }
    throw "Unable to retrive card command from webserver. Check your internet connection and try again";
}

async function getPersonalizationCommand(cardId, commandName, request)
{
    try {
        const response = await fetch(`${baseUrl}/generateCommand/${cardId}/${channel}/${commandName}`, {
            method: 'POST',
            headers: {
                'X-Authorization': apiKey,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(request)
        })
        return await response.json()
    } catch(ex) {
        console.log(ex);
    }
    throw "Unable to retrive card command from webserver. Check your internet connection and try again";
}

//**************************DOM**************************//
function showSpinner(){
    _target.style.display = 'block';
}

function hideSpinner(){
    _target.style.display = 'none';
}

function manageMessages(dest, type, message)
{
    d.querySelector(dest).innerHTML = message;
    if (type == "s")
    {
        d.querySelector(dest).classList.replace("text-danger", "text-success");
        d.querySelector(dest).classList.replace("bg-danger", "bg-success");
        d.querySelector(dest).classList.replace("bg-light-danger", "bg-light-success");
    }
    else if (type == "d")
    {
        d.querySelector(dest).classList.replace("text-success", "text-danger");
        d.querySelector(dest).classList.replace("bg-success", "bg-danger");
        d.querySelector(dest).classList.replace("bg-light-success", "bg-light-danger");
    }
}

const countDownClock = (number = 100, format = 'seconds') => {
    let countdown;
    timer(number);
    function timer(seconds) {
        const now = Date.now();
        const then = now + seconds * 1000;
        countdown = setInterval(() => {
            const secondsLeft = Math.round((then - Date.now()) / 1000);
            if(secondsLeft <= 0) {
                clearInterval(countdown);
                d.querySelector('#opt').innerHTML = '';
                return;
            };
            displayTimeLeft(secondsLeft);
        },1000);
    }
    function displayTimeLeft(seconds) {
        d.querySelector('.seconds').textContent = seconds % 60 < 10 ? `0${seconds % 60}` : seconds % 60;
    }
}

//**************************INTERFACE**************************//

document.addEventListener("DOMContentLoaded", (event) => {
    listReaders();
});

async function loadInterface(reader)
{
    showSpinner();
    manageMessages("#h_mess", "s", "Card detected");
    manageMessages("#t_mess", null, "Card inserted in " + reader.name);
    manageMessages("#b_mess", null, "ATR detected");
    manageMessages("#f_mess", "s", "Wait while recognize card type!");
    try{
        const check = await checkCardByAtr(reader.atr);
        if(!check || !check.Id)
            throw 'Card id not found';
        if(Array.isArray(check.Id) && check.Id.length > 1) {
            if(!check.GetVersion)
                throw 'Needed command GetVersion not found'
            const idIdx = await execOnReader(reader, {
                name: 'GetVersion',
                command: check.GetVersion
            })
            _cardId = check.Id[idIdx[0].result.cmdIdx]
            _cardType = check.Type[idIdx[0].result.cmdIdx];
        } else {
            _cardId = check.Id[0]
            _cardType = check.Type[0];
        }
        _reader = reader;
        fetchPartialPage('/authentica'+_cardType, '#card_div');
        hideSpinner();
    } catch(ex){
        manageMessages("#b_mess", "d", ex);
    }
}

function unloadInterface(reader)
{
    if (reader == _reader)
        {
            manageMessages("#h_mess", "d", "Card removed");
            manageMessages("#t_mess", null, "Card removed in " + reader.name);
            manageMessages("#b_mess", null, "");
            manageMessages("#f_mess", "d", "Put your card on the reader to continue working");
            d.querySelector('#card_div').innerHTML = "";
            _cardId = null;
            _cardType = null;
            _reader.disconnect();
            _reader = null;
        }
}

function fetchPartialPage(url, dest)
{
    fetch(url).then(function (response) {
        return response.text();
    }).then(function (html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        d.querySelector(dest).innerHTML = doc.body.innerHTML;
        d.querySelector("#b_mess").innerHTML = "Card data loaded";
        executePartialScript(_cardType);
    }).catch(function (ex) {
        manageMessages("#b_mess", "d", "error loading card interface. Check your internet connection and try again");
    }
    );
}

async function executePartialScript(card_type)
{
    switch(card_type)
    {
        case 'F':
            showSpinner();
            try{
                const ex = new Promise(resolve => resolve(CmdsExecutor(_reader, "[SelectBeCard][ReadSequenceInfo]")));
                const execResult = await ex;
                if(execResult && Array.isArray(execResult) && execResult.length > 1)
                {
                    if (execResult[0].status != "ok")
                        throw 'Applet not found';
                    d.querySelector("#f_mess").innerHTML = "Card selected";
                    d.querySelector("#f_mess").classList.replace("text-danger", "text-success");
                    if (execResult[1].status == "ok")
                    {
                        d.querySelector("#b_mess").innerHTML = "Card data loaded";
                        execResult[1].result.cmdRes.forEach((sequence, index) => {
                            d.querySelector("#sequences").add(new Option(sequence, sequence));
                        })
                    }
                }
            } catch(ex) {
                manageMessages("#b_mess", "d", ex);
            } finally {
                hideSpinner();
            }
            d.querySelector('#getOtp').addEventListener('click', async function(event){
                try{
                    let sequence = d.querySelector("#sequences").value;
                    if (!sequence)
                        throw "Select a sequence to get OTP";
                    if (await verifyTokenByEmail(sequence).result == "0")
                        throw "OTP sequence not associated to your email";
                    d.querySelector("#imgfinger").classList.replace('imgfinger_hidden', 'imgfinger_request');

                const ex = new Promise(resolve => resolve(CmdsExecutor(_reader, "[SelectBeCard][ReadOtpToken {OtpMode="+sequence+"}]")));
                execResult = await ex;
                if(execResult && Array.isArray(execResult) && execResult.length > 1)
                {
                    if (execResult[0].status != "ok")
                        throw 'Applet not found';
                    if (execResult[1].status == "ok" && execResult[1].result.cmdRes[0] !== undefined)
                        {
                            d.querySelector("#otp").innerHTML = 'OTP: '+execResult[1].result.cmdRes[0];
                            countDownClock(30);
                        }
                        else
                            throw "OTP not generated by card";
                }
                } catch(ex) {
                    manageMessages("#b_mess", "d", ex);
                } finally {
                    d.querySelector("#imgfinger").classList.replace('imgfinger_request', 'imgfinger_hidden');
                }
            });
            break;
        case 'C':
            break;
        default:
            break;
    }
}

