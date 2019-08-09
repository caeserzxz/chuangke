function pwdLevel(value) {
    var pattern_1 = /^.*([\W_])+.*$/i;
    var pattern_2 = /^.*([a-zA-Z])+.*$/i;
    var pattern_3 = /^.*([0-9])+.*$/i;
    var level = 0;
    if (value.length > 10) {
        level++;
    }
    if (pattern_1.test(value)) {
        level++;
    }
    if (pattern_2.test(value)) {
        level++;
    }
    if (pattern_3.test(value)) {
        level++;
    }
    if (level > 3) {
        level = 3;
    }
    return level;
}
// ����
function sleepm(numberMillis) {
    var now = new Date();
    var exitTime = now.getTime() + numberMillis;
    while (true) {
        now = new Date();
        if (now.getTime() > exitTime) return;
    }
}
var weakPwdArray = ["123456", "123456789", "111111", "5201314", "12345678", "123123", "password", "1314520", "123321", "7758521", "1234567", "5211314", "666666", "520520", "woaini", "520131", "11111111", "888888", "hotmail.com", "112233", "123654", "654321", "1234567890", "a123456", "88888888", "163.com", "000000", "yahoo.com.cn", "sohu.com", "yahoo.cn", "111222tianya", "163.COM", "tom.com", "139.com", "wangyut2", "pp.com", "yahoo.com", "147258369", "123123123", "147258", "987654321", "100200", "zxcvbnm", "123456a", "521521", "7758258", "111222", "110110", "1314521", "11111111", "12345678", "a321654", "111111", "123123", "5201314", "00000000", "q123456", "123123123", "aaaaaa", "a123456789", "qq123456", "11112222", "woaini1314", "a123123", "a111111", "123321", "a5201314", "z123456", "liuchang", "a000000", "1314520", "asd123", "88888888", "1234567890", "7758521", "1234567", "woaini520", "147258369", "123456789a", "woaini123", "q1q1q1q1", "a12345678", "qwe123", "123456q", "121212", "asdasd", "999999", "1111111", "123698745", "137900", "159357", "iloveyou", "222222", "31415926", "123456", "111111", "123456789", "123123", "9958123", "woaini521", "5201314", "18n28n24a5", "abc123", "password", "123qwe", "123456789", "12345678", "11111111", "dearbook", "00000000", "123123123", "1234567890", "88888888", "111111111", "147258369", "987654321", "aaaaaaaa", "1111111111", "66666666", "a123456789", "11223344", "1qaz2wsx", "xiazhili", "789456123", "password", "87654321", "qqqqqqqq", "000000000", "qwertyuiop", "qq123456", "iloveyou", "31415926", "12344321", "0000000000", "asdfghjkl", "1q2w3e4r", "123456abc", "0123456789", "123654789", "12121212", "qazwsxedc", "abcd1234", "12341234", "110110110", "asdasdasd", "123456", "22222222", "123321123", "abc123456", "a12345678", "123456123", "a1234567", "1234qwer", "qwertyui", "123456789a", "qq.com", "369369", "163.com", "ohwe1zvq", "xiekai1121", "19860210", "1984130", "81251310", "502058", "162534", "690929", "601445", "1814325", "as1230", "zz123456", "280213676", "198773", "4861111", "328658", "19890608", "198428", "880126", "6516415", "111213", "195561", "780525", "6586123", "caonima99", "168816", "123654987", "qq776491", "hahabaobao", "198541", "540707", "leqing123", "5403693", "123456", "123456789", "111111", "5201314", "123123", "12345678", "1314520", "123321", "7758521", "1234567", "5211314", "520520", "woaini", "520131", "666666", "RAND#a#8", "hotmail.com", "112233", "123654", "888888", "654321", "1234567890", "a123456"];

function verc() {
    $("#JD_Verification1").click();
}
function verc2() {
    $("#JD_Verification2").click();
}
var validateRegExp = {
    decmal: "^([+-]?)\\d*\\.\\d+$",
    // ������
    decmal1: "^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*$",
    // ��������
    decmal2: "^-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*)$",
    // ��������
    decmal3: "^-?([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0)$",
    // ������
    decmal4: "^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0$",
    // �Ǹ����������������� + 0��
    decmal5: "^(-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*))|0?.0+|0$",
    // �������������������� +
    // 0��
    intege: "^-?[1-9]\\d*$",
    // ����
    intege1: "^[1-9]\\d*$",
    // ������
    intege2: "^-[1-9]\\d*$",
    // ������
    num: "^([+-]?)\\d*\\.?\\d+$",
    // ����
    num1: "^[1-9]\\d*|0$",
    // ������������ + 0��
    num2: "^-[1-9]\\d*|0$",
    // ������������ + 0��
    ascii: "^[\\x00-\\xFF]+$",
    // ��ACSII�ַ�
    chinese: "^[\\u4e00-\\u9fa5]+$",
    // ������
    color: "^[a-fA-F0-9]{6}$",
    // ��ɫ
    date: "^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$",
    // ����
    email: "^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$",
    // �ʼ�
    idcard: "^[1-9]([0-9]{14}|[0-9]{17})$",
    // ���֤
    ip4: "^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$",
    // ip��ַ
    letter: "^[A-Za-z]+$",
    // ��ĸ
    letter_l: "^[a-z]+$",
    // Сд��ĸ
    letter_u: "^[A-Z]+$",
    // ��д��ĸ
    mobile: "^0?(13|15|18|14|17)[0-9]{9}$",
    // �ֻ�
    notempty: "^\\S+$",
    // �ǿ�
    password: "^.*[A-Za-z0-9\\w_-]+.*$",
    // ����
    fullNumber: "^[0-9]+$",
    // ����
    picture: "(.*)\\.(jpg|bmp|gif|ico|pcx|jpeg|tif|png|raw|tga)$",
    // ͼƬ
    qq: "^[1-9]*[1-9][0-9]*$",
    // QQ����
    rar: "(.*)\\.(rar|zip|7zip|tgz)$",
    // ѹ���ļ�
    tel: "^[0-9\-()����]{7,18}$",
    // �绰����ĺ���(������֤��������,��������,�ֻ���)
    url: "^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$",
    // url
    username: "^[A-Za-z0-9_\\-\\u4e00-\\u9fa5]+$",
    // ����
    deptname: "^[A-Za-z0-9_()����\\-\\u4e00-\\u9fa5]+$",
    // ��λ��
    zipcode: "^\\d{6}$",
    // �ʱ�
    realname: "^[A-Za-z\\u4e00-\\u9fa5]+$",
    // ��ʵ����
    companyname: "^[A-Za-z0-9_()����\\-\\u4e00-\\u9fa5]+$",
    companyaddr: "^[A-Za-z0-9_()����\\#\\-\\u4e00-\\u9fa5]+$",
    companysite: "^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&#=]*)?$"
};
// ������
(function($) {
    $.fn.jdValidate = function(option, callback, def) {
        var ele = this;
        var id = ele.attr("id");
        var type = ele.attr("type");
        var rel = ele.attr("rel");
        var _onFocus = $("#" + id + validateSettings.onFocus.container);
        var _succeed = $("#" + id + validateSettings.succeed.container);
        var _isNull = $("#" + id + validateSettings.isNull.container);
        var _error = $("#" + id + validateSettings.error.container);
        if (def == true) {
            var str = ele.val();
            var tag = ele.attr("sta");
            if (str == "" || str == "-1") {
                validateSettings.isNull.run({
                    prompts: option,
                    element: ele,
                    isNullEle: _isNull,
                    succeedEle: _succeed
                },
                option.isNull);
            } else if (tag == 1 || tag == 2) {
                return;
            } else {
                callback({
                    prompts: option,
                    element: ele,
                    value: str,
                    errorEle: _error,
                    succeedEle: _succeed
                });
            }
        } else {
            if (typeof def == "string") {
                ele.val(def);
            }
            if (type == "checkbox" || type == "radio") {
                if (ele.attr("checked") == true) {
                    ele.attr("sta", validateSettings.succeed.state);
                }
            }
            switch (type) {
            case "text":
            case "password":
                ele.bind("focus",
                function() {
                    var str = ele.val();
                    if (str == def) {
                        ele.val("");
                    }
                    validateSettings.onFocus.run({
                        prompts: option,
                        element: ele,
                        value: str,
                        onFocusEle: _onFocus,
                        succeedEle: _succeed
                    },
                    option.onFocus, option.onFocusExpand);
                }).bind("blur",
                function() {
                    var str = ele.val();
                    if (str == "") {
                        ele.val(def);
                    }
                    if (validateRules.isNull(str)) {
                        validateSettings.isNull.run({
                            prompts: option,
                            element: ele,
                            value: str,
                            isNullEle: _isNull,
                            succeedEle: _succeed
                        },
                        "");
                    } else {
                        callback({
                            prompts: option,
                            element: ele,
                            value: str,
                            errorEle: _error,
                            isNullEle: _isNull,
                            succeedEle: _succeed
                        });
                    }
                });
                break;
            default:
                if (rel && rel == "select") {
                    ele.bind("change",
                    function() {
                        var str = ele.val();
                        callback({
                            prompts: option,
                            element: ele,
                            value: str,
                            errorEle: _error,
                            isNullEle: _isNull,
                            succeedEle: _succeed
                        });
                    })
                } else {
                    ele.bind("click",
                    function() {
                        callback({
                            prompts: option,
                            element: ele,
                            errorEle: _error,
                            isNullEle: _isNull,
                            succeedEle: _succeed
                        });
                    })
                }
                break;
            }
        }
    }
})(jQuery);

// ����
var validateSettings = {
    onFocus: {
        state: null,
        container: "_error",
        style: "focus",
        run: function(option, str, expands) {
            if (!validateRules.checkType(option.element)) {
                option.element.removeClass(validateSettings.INPUT_style2).addClass(validateSettings.INPUT_style1);
            }
            option.succeedEle.removeClass(validateSettings.succeed.style);
            option.onFocusEle.removeClass().addClass(validateSettings.onFocus.style).html(str);
            if (expands) {
                expands();
            }
        }
    },
    isNull: {
        state: 0,
        container: "_error",
        style: "null",
        run: function(option, str) {
            option.element.attr("sta", 0);
            if (!validateRules.checkType(option.element)) {
                if (str == "") {
                    option.element.removeClass(validateSettings.INPUT_style2).removeClass(validateSettings.INPUT_style1);
                } else {
                    option.element.removeClass(validateSettings.INPUT_style1).addClass(validateSettings.INPUT_style2);
                }
            }

            option.succeedEle.removeClass(validateSettings.succeed.style);
            if (str == "") {
                option.isNullEle.removeClass().addClass(validateSettings.isNull.style).html(str);
            } else {
                option.isNullEle.removeClass().addClass(validateSettings.error.style).html(str);
            }
        }
    },
    error: {
        state: 1,
        container: "_error",
        style: "error",
        run: function(option, str) {
            option.element.attr("sta", 1);
            if (!validateRules.checkType(option.element)) {
                option.element.removeClass(validateSettings.INPUT_style1).addClass(validateSettings.INPUT_style2);
            }

            option.succeedEle.removeClass(validateSettings.succeed.style);
            option.errorEle.removeClass().addClass(validateSettings.error.style).html(str);
        }
    },
    succeed: {
        state: 2,
        container: "_succeed",
        style: "succeed",
        run: function(option) {
            option.element.attr("sta", 2);
            option.errorEle.empty();
            if (!validateRules.checkType(option.element)) {
                option.element.removeClass(validateSettings.INPUT_style1).removeClass(validateSettings.INPUT_style2);
            }

            option.succeedEle.addClass(validateSettings.succeed.style);
            option.errorEle.removeClass();
        }
    },
    INPUT_style1: "highlight1",
    INPUT_style2: "highlight2"
}

// ��֤����
var validateRules = {
    isNull: function(str) {
        return (str == "" || typeof str != "string");
    },
    betweenLength: function(str, _min, _max) {
        return (str.length >= _min && str.length <= _max);
    },
    isUid: function(str) {
        return new RegExp(validateRegExp.username).test(str);
    },
    fullNumberName: function(str) {
        return new RegExp(validateRegExp.fullNumber).test(str);
    },
    isPwd: function(str) {
        return /^.*([\W_a-zA-z0-9-])+.*$/i.test(str);
    },
    isPwdRepeat: function(str1, str2) {
        return (str1 == str2);
    },
    isEmail: function(str) {
        return new RegExp(validateRegExp.email).test(str);
    },
    isTel: function(str) {
        return new RegExp(validateRegExp.tel).test(str);
    },
    isMobile: function(str) {
        return new RegExp(validateRegExp.mobile).test(str);
    },
    checkType: function(element) {
        return (element.attr("type") == "checkbox" || element.attr("type") == "radio" || element.attr("rel") == "select");
    },
    isRealName: function(str) {
        return new RegExp(validateRegExp.realname).test(str);
    },
    isCompanyname: function(str) {
        return new RegExp(validateRegExp.companyname).test(str);
    },
    isCompanyaddr: function(str) {
        return new RegExp(validateRegExp.companyaddr).test(str);
    },
    isCompanysite: function(str) {
        return new RegExp(validateRegExp.companysite).test(str);
    },
    simplePwd: function(str) {
        // var pin = $("#regName").val();
        // if (pin.length > 0) {
        // pin = strTrim(pin);
        // if (pin == str) {
        // return true;
        // }
        // }
        return pwdLevel(str) == 1;
    },
    weakPwd: function(str) {
        for (var i = 0; i < weakPwdArray.length; i++) {
            if (weakPwdArray[i] == str) {
                return true;
            }
        }
        return false;
    }
};
// ��֤�ı�
var validatePrompt = {
    regName: {
        //onFocus: "4-20λ�ַ���֧����Ӣ�ġ����ּ�\"-\"��\"_\"���",
        onFocus:"4-20λ�ַ�,֧�ֺ��֡���ĸ�����ּ�\"-\"��\"_\"���",
        succeed: "",
        isNull: "�������û���",
        error: {
            beUsed: "���û����ѱ�ʹ�ã����������롣������Ǹ��û���������<a href='https://passport.jd.com/uc/login' class='flk13'>��¼</a>",
            badLength: "�û�������ֻ����4-20λ�ַ�֮��",
            badFormat: "�û���ֻ�������ġ�Ӣ�ġ����ּ�\"-\"��\"_\"���",
            fullNumberName: "�û��������Ǵ����֣�����������"
        },
        onFocusExpand: function() {
            $("#morePinDiv").removeClass().addClass("intelligent-error hide");
        }
    },

    pwd: {
        onFocus: "<span>6-20λ�ַ�����������ĸ�����ֺͷ��������������</span>",
        succeed: "",
        isNull: "����������",
        error: {
            badLength: "���볤��ֻ����6-20λ�ַ�֮��",
            badFormat: "����ֻ����Ӣ�ġ����ּ����������",
            simplePwd: "<span>������Ƚϼ򵥣��б������գ�����������Ϊ�������룬����ĸ+���ֵ����</span>",
            weakPwd: "<span>������Ƚϼ򵥣��б������գ�����������Ϊ��������</span>"
        },
        onFocusExpand: function() {
            $("#pwdstrength").hide();
        }
    },
    pwdRepeat: {
        onFocus: "���ٴ���������",
        succeed: "",
        isNull: "��ȷ������",
        error: {
            badLength: "���볤��ֻ����6-20λ�ַ�֮��",
            badFormat2: "�����������벻һ��",
            badFormat1: "����ֻ����Ӣ�ġ����ּ����������"
        }
    },
    phone: {
        onFocus: "�������ֻ�����",
        succeed: "",
        isNull: "�������ֻ�����",
        error: ""
    },
    protocol: {
        onFocus: "",
        succeed: "",
        isNull: "�����Ķ���ͬ�⡶�����û�ע��Э�顷",
        error: ""
    },
    empty: {
        onFocus: "",
        succeed: "",
        isNull: "",
        error: ""
    }
};

var nameold, morePinOld, emailResult;
var namestate = false;
// �ص�����
var validateFunction = {
    regName: function(option) {
        $("#intelligent-regName").empty().hide();
        var regName = option.value;
        if (validateRules.isNull(regName) || regName == '') {
            option.element.removeClass(validateSettings.INPUT_style2).removeClass(validateSettings.INPUT_style1);
            $("#regName_error").removeClass().empty();
            return;
        }
        $("#authcodeDiv").show();
        checkPin(option);
    },

    pwd: function(option) {
        var str1 = option.value;
        var regName = $("#regName").val();
        if ((validateRules.isNull(regName) == false) && (regName != '') && regName == str1) {
            $("#pwdstrength").hide();
            validateSettings.error.run(option, "<span>�����������˻���Ϣ̫�غϣ��б������գ��뻻һ������</span>");
            return;
        }

        //var str2 = $("#pwdRepeat").val();
        $("#pwdRepeat").blur();
        var format = validateRules.isPwd(option.value);
        var length = validateRules.betweenLength(option.value, 6, 20);

        $("#pwdstrength").hide();
        if (!length && format) {
            validateSettings.error.run(option, option.prompts.error.badLength);
        } else if (!length && !format) {
            validateSettings.error.run(option, option.prompts.error.badFormat);
        } else if (length && !format) {
            validateSettings.error.run(option, option.prompts.error.badFormat);
        } else if (validateRules.weakPwd(str1)) {
            validateSettings.error.run(option, option.prompts.error.weakPwd);
        } else {

            validateSettings.succeed.run(option);
            validateFunction.pwdstrength();
            if (validateRules.simplePwd(str1)) {
                $("#pwd_error").removeClass().addClass("focus");
                $("#pwd_error").empty().html(option.prompts.error.simplePwd);
                return;
            }
        }
        //		if (str2 == str1) {
        //			$("#pwdRepeat").focus();
        //		}
    },
    pwdRepeat: function(option) {
        var str1 = option.value;
        var str2 = $("#pwd").val();
        var length = validateRules.betweenLength(option.value, 6, 20);
        var format2 = validateRules.isPwdRepeat(str1, str2);
        var format1 = validateRules.isPwd(str1);
        if (!length) {
            validateSettings.error.run(option, option.prompts.error.badLength);
        } else {
            if (!format1) {
                validateSettings.error.run(option, option.prompts.error.badFormat1);
            } else {
                if (!format2) {
                    validateSettings.error.run(option, option.prompts.error.badFormat2);
                } else {
                    validateSettings.succeed.run(option);
                }
            }
        }
    },
    // mobileCode: function(option) {
    // var bool = validateRules.isNull(option.value);
    // if (bool) {
    // validateSettings.error.run(option, option.prompts.error);
    // return;
    // } else {
    // validateSettings.succeed.run(option);
    // }
    // },
    protocol: function(option) {
        if (option.element.attr("checked") == true) {
            option.element.attr("sta", validateSettings.succeed.state);
            option.errorEle.html("");
        } else {
            option.element.attr("sta", validateSettings.isNull.state);
            option.succeedEle.removeClass(validateSettings.succeed.style);
        }
    },
    pwdstrength: function() {
        var element = $("#pwdstrength");
        var value = $("#pwd").val();
        if (value.length >= 6 && validateRules.isPwd(value)) {
            $("#pwd_error").removeClass('focus');
            $("#pwd_error").empty();
            element.show();
            var level = pwdLevel(value);
            switch (level) {
            case 1:
                element.removeClass().addClass("strengthA");
                break;
            case 2:
                element.removeClass().addClass("strengthB");
                break;
            case 3:
                element.removeClass().addClass("strengthC");
                break;
            default:
                break;
            }
        } else {
            element.hide();
        }
    },
    checkGroup: function(elements) {
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].checked) {
                return true;
            }
        }
        return false;
    },
    checkSelectGroup: function(elements) {
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].value == -1) {
                return false;
            }
        }
        return true;
    },

    FORM_submit: function(elements) {
        var bool = true;
        for (var i = 0; i < elements.length; i++) {
            if ($(elements[i]).attr("sta") == 2) {
                bool = true;
            } else {
                bool = false;
                break;
            }
        }

        return bool;
    }
};

// ����û���
var checkpin = -10;
function checkPin(option) {
    var pin = option.value;
    if (!validateRules.betweenLength(pin.replace(/[^\x00-\xff]/g, "**"), 4, 20)) {
        validateSettings.error.run(option, option.prompts.error.badLength);
        return false;
    }

    if (!validateRules.isUid(pin)) {
        validateSettings.error.run(option, option.prompts.error.badFormat);
        return;
    }
    if (validateRules.fullNumberName(pin)) {
        validateSettings.error.run(option, option.prompts.error.fullNumberName);
        return;
    }
    if (!namestate || nameold != pin) {
        if (nameold != pin) {
            nameold = pin;
            option.errorEle.html("<em style='color:#999'>�����С���</em>");
            $.getJSON("../validateuser/isPinEngaged?pin=" + escape(pin) + "&r=" + Math.random(),
            function(date) {
                checkpin = date.success;
                if (date.success == 0) {
                    validateSettings.succeed.run(option);
                    namestate = true;
                } else if (date.success == 2) {
                    validateSettings.error.run(option, "�û��������˷Ƿ���");
                    namestate = false;
                } else {
                    validateSettings.error.run(option, "<span>" + option.prompts.error.beUsed.replace("{1}", option.value) + "</span>");
                    namestate = false;
                    morePinOld = date.morePin;
                    if (date.morePin != null && date.morePin.length > 0) {
                        var html = ""
                        for (var i = 0; i < date.morePin.length; i++) {
                            html += "<div class='item-fore'><input name='morePinRadio' onclick='selectMe(this);' type='radio' class='radio' value='" + date.morePin[i] + "'/><label>" + date.morePin[i] + "</label></div>"
                        }
                        $("#morePinGroom").empty();
                        $("#morePinGroom").html(html);
                        $("#morePinDiv").removeClass().addClass("intelligent-error");
                    }
                }
            });
        } else {

            if (checkpin == 2) {
                validateSettings.error.run(option, "�û��������˷Ƿ���");
            } else {
                validateSettings.error.run(option, "<span>" + option.prompts.error.beUsed.replace("{1}", option.value) + "</span>");
                if (morePinOld != null && morePinOld.length > 0) {
                    $("#morePinDiv").removeClass().addClass("intelligent-error");
                }
            }
            namestate = false;
        }
    } else {
        validateSettings.succeed.run(option);
    }
}

function selectMe(option) {
    $("#morePinDiv").removeClass().addClass("intelligent-error hide");
    $("#regName").val(option.value);
    $("#regName").blur();
}
// �����̷����ֻ���֤��
function sendMobileCode() {
    if ($("#sendMobileCode").attr("disabled")) {
        return;
    }
    mobileCodeHide();
    var mobile = $("#phone").val();
    if (validateRules.isNull(mobile)) {
        $("#phone_error").removeClass().addClass("error").html("�������ֻ���");
        $("#phone_error").show();
        return;
    }
    if (!validateRules.isMobile(mobile)) {
        $("#phone_error").removeClass().addClass("error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone_error").show();
        return;
    }
    $('#mobileCode').removeClass("highlight2");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {
        if (result.success == 0) {
            $('#phone').removeClass().addClass("text");
            $("#phone_error").html("");
            $("#phone_error").hide();
            $("#phone_succeed").removeClass().addClass("blank succeed");
            mobileFlags = true;
            sendmCode();
        }

        if (result.success == 1) {
            $('#phone').removeClass().addClass('text highlight3');
            $("#phone_error").html("�ֻ�����ע�ᣬ����ע�Ὣ��ԭ�˺Ž��");
            $("#phone_error").removeClass().addClass("cue");
            $("#phone_error").show();
            $("#phone_succeed").removeClass().addClass("blank cue-ico");
            mobileFlags = false;
            var state = $("#state").val();
            if (state == "unbind") {
                sendmCode();
            } else {
                mobileEngagedStyle();
            }
        }

        if (result.success == 2) {
            $('#phone').removeClass().addClass('text highlight2');
            $("#phone_error").html("���ֻ����ѱ������˻��󶨣�"+result.ub+"���ڲ��ɸİ�");
            $("#phone_error").removeClass().addClass("error");
            $("#phone_error").show();
            $("#phone_succeed").removeClass().addClass("");
            mobileFlags = false;
        }
    });

}
// �ֻ�ע�ᷢ����֤��target
function sendmCode() {
    if ($("#sendMobileCode").attr("disabled") || delayFlag == false) {
        return;
    }
    var state = $("#state").val();
    if (state != "unbind") {
        $("#rebind").remove();
        $("#mobileCodeDiv").show();
    }
    $("#sendMobileCode").attr("disabled", "disabled");
    jQuery.ajax({
        type: "get",
        url: "../notifyuser/mobileCode?state=" + state + "&mobile=" + $("#phone").val() + "&r=" + Math.random(),
        success: function(result) {
            if (result) {
                var obj = eval(result);
                if (obj.rs == 1 || obj.remain) {
                    $("#mobileCode_error").addClass("hide");
                    $("#dyMobileButton").html("120������»�ȡ");
                    if (obj.remain) {
                        $("#mobileCodeSucMessage").empty().html(obj.remain);
                    } else {
                        if (state == "unbind") {
                            $("#mobileCode_error").removeClass().addClass("cue").empty().html("У�����ѷ���,ע��ɹ����ֻ��Ž���ԭ�ʺŽ��");
                            $("#mobileCode_error").show();
                        } else {
                            $("#mobileCode_error").removeClass().empty().html("��֤���ѷ��ͣ�����ն��š�");
                            $("#mobileCode_error").show();
                        }
                    }

                    setTimeout(countDown, 1000);
                    $("#sendMobileCode").removeClass().addClass("btn btn-15").attr("disabled", "disabled");
                    $("#mobileCode").removeAttr("disabled");
                }
                if (obj.rs == -1) {
                    mobileCodeError("���緱æ�����Ժ����»�ȡ��֤��");
                }
                if (obj.info) {
                    if (obj.info == "���ֻ����ѱ�ʹ�ã����������") {
                        mobileEngagedStyle();
                    } else {
                        mobileCodeError(obj.info);
                    }

                }

                if (obj.rs == -2) {
                    mobileCodeError("���緱æ�����Ժ����»�ȡ��֤��");
                }
            }
        }
    });
}
// ������֤������֤��target
function sendmCode1() {
    if ($("#sendMobileCode1").attr("disabled") || delayFlag1 == false) {
        return;
    }
    $("#rebind1").remove();
    $("#mobileCodeDiv1").show();
    $("#sendMobileCode1").attr("disabled", "disabled");
    var state = $("#state").val();
    jQuery.ajax({
        type: "get",
        url: "../notifyuser/mobileCode?state=" + state + "&mobile=" + $("#phone1").val() + "&r=" + Math.random(),
        success: function(result) {
            if (result) {
                var obj = eval(result);
                if (obj.rs == 1 || obj.remain) {
                    $("#mobileCode1_error").addClass("hide");
                    $("#dyMobileButton1").html("120������»�ȡ");
                    if (obj.remain) {
                        $("#mobileCodeSucMessage1").empty().html(obj.remain);
                    } else {
                        if (state == "unbind") {
                            $("#mobileCodeSucMessage1").removeClass().addClass("cue").empty().html("У�����ѷ���,ע��ɹ����ֻ��Ž���ԭ�ʺŽ��");
                        } else {
                            $("#mobileCodeSucMessage1").empty().html("��֤���ѷ��ͣ�����ն��š�");
                        }
                    }

                    setTimeout(countDown1, 1000);
                    $("#sendMobileCode1").removeClass().addClass("btn btn-15").attr("disabled", "disabled");
                    $("#mobileCode1").removeAttr("disabled");
                }
                if (obj.rs == -1) {
                    $("#mobileCode1_error").html("���緱æ�����Ժ����»�ȡ��֤��");
                    $("#mobileCode1_error").removeClass().addClass("error");
                    $("#mobileCode1_error").show();
                    $("#sendMobileCode1").removeClass().addClass("btn").removeAttr("disabled");
                }
                if (obj.info) {
                    if (obj.info == "���ֻ����ѱ�ʹ�ã����������") {
                        mobileEngagedStyle1();
                    } else {
                        $("#mobileCode1_error").html(obj.info);
                        $("#mobileCode1_error").removeClass().addClass("error");
                        $("#mobileCode1_error").show();
                        $("#sendMobileCode1").removeClass().addClass("btn").removeAttr("disabled");
                    }
                }

                if (obj.rs == -2) {
                    $("#mobileCode1_error").html("���緱æ�����Ժ����»�ȡ��֤��");
                    $("#mobileCode1_error").removeClass().addClass("error");
                    $("#mobileCode1_error").show();
                    $("#sendMobileCode1").removeClass().addClass("btn").removeAttr("disabled");
                }
            }
        }
    });
}
// �����̷����ֻ���֤��
function sendMobileCode1() {
    if ($("#sendMobileCode1").attr("disabled")) {
        return;
    }
    var mobile = $("#phone1").val();
    if (validateRules.isNull(mobile)) {
        $('#phone1').addClass('highlight2');
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        $("#phone1_error").removeClass().addClass("error").html("�������ֻ���");
        $("#phone1_error").show();
        return;
    }
    if (!validateRules.isMobile(mobile)) {
        $("#phone1_error").removeClass().addClass("error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone1_error").show();
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        return;
    }

    var mobile = $("#phone1").val();
    if (mobile == "") {
        $('#phone1').removeClass().addClass("text");
        $("#phone1_error").hide();
        $('#phone1_succeed').removeClass('error-ico');
        mobileFlag = false;
        return;
    }
    if (!validateRules.isMobile(mobile)) {
        $("#phone1_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone1_error").removeClass().addClass("error");
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        $("#phone1_error").show();
        $('#phone1').removeClass("highlight1").addClass('highlight2');
        mobileFlag = false;
        return;
    }
    $("#mobileCode1_error").removeClass().empty();
    $("#mobileCode1_error").hide();
    $('#mobileCode1').removeClass("highlight2");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {
        if (result.success == 0) {
            $('#phone1').removeClass().addClass("text");
            $("#phone1_error").html("");
            $("#phone1_error").hide();
            $("#phone1_succeed").removeClass().addClass("blank succeed");
            mobileFlags = true;
            sendmCode1();
            return;
        }
        if (result.success == 1) {
            $('#phone1').removeClass().addClass('text highlight3');
            $("#phone1_error").html("�ֻ�����ע�ᣬ����ע�Ὣ��ԭ�˺Ž��");
            $("#phone1_error").removeClass().addClass("cue");
            $("#phone1_error").show();
            $("#phone1_succeed").removeClass().addClass("blank cue-ico");
            mobileFlags = false;
            var state = $("#state").val();
            if (state == "unbind") {
                sendmCode1();
            } else {
                mobileEngagedStyle1();
            }
            return;
        }
        if (result.success == 2) {
            $('#phone1').removeClass().addClass('text highlight2');
            $("#phone_error").html("���ֻ����ѱ������˻��󶨣�"+result.ub+"���ڲ��ɸİ�");
            $("#phone1_error").removeClass().addClass("error");
            $("#phone1_error").show();
            $("#phone1_succeed").removeClass().addClass("blank error-ico");
            // $("#sendMobileCode1").attr("disabled", "disabled");
            mobileFlags = false;
        }
    });
}

var oldEmail, emailCheckResult;
// ������֤��Ϣ��д
function sendRegMail() {
    var mail = $("#mail").val();
    var authcode1 = $("#authcode1").val();
    if (mail == "") {
        $("#mail_error").removeClass().addClass("error").html("����������");
        $("#mail_error").show();
        $('#mail_succeed').addClass('error-ico');
        $('#mail').addClass('highlight2');
        return;
    }
    var email = strTrim(mail);
    var format = validateRules.isEmail(email);
    var format2 = validateRules.betweenLength(email, 0, 50);
    if (!format) {
        $("#mail_error").html("�����ַ����ȷ������������");
        $('#mail_succeed').addClass('error-ico');
        $('#mail').addClass('highlight2');
        return;
    } else {
        if (!format2) {
            $('#mail_error').removeClass().addClass("error");
            $("#mail_error").html("�����ַ����Ӧ��4-50���ַ�֮��");
            $('#mail_succeed').addClass('error-ico');
            $('#mail').removeClass("highlight1").addClass('highlight2');
            return;
        } else {
            // if (oldEmail == email) {
            // if (emailCheckResult == 1) {
            // emailEngagedStyle();
            // return;
            // }
            // if (emailCheckResult == 2) {
            // emailFormatErrorStyle();
            // return;
            // }
            // return;
            // }
            // oldEmail = email;
            $.getJSON("../validateuser/isEmailEngaged?email=" + escape(email) + "&r=" + Math.random(),
            function(result) {
                emailResult = result.success;
                emailCheckResult = emailResult;
                // ����δ����֤ ��ע��
                if (emailResult == 0) {
                    $("#emailMg").val(email);
                    $("#authcodeMg").val(authcode1);
                    jdThickBoxclose();
                    $("#dyMobileButton1").html("��ȡ������֤��");
                    jQuery.jdThickBox({
                        type: "text",
                        width: 500,
                        height: 260,
                        source: $('#box01').html(),
                        title: "��֤�ֻ�",
                        _close_val: "��",
                        _con: "opinioncon",
                        _titleOn: true
                    });
                }
                if (emailResult == 1) {
                    emailEngagedStyle();
                    return;
                }
                if (emailResult == 2) {
                    emailFormatErrorStyle();
                    return;
                }
            });

        }
    }
}

function emailEngagedStyle() {
    $('#mail_succeed').addClass('error-ico');
    $('#mail_error').removeClass().addClass("error");
    $("#mail_error").html("�������ѱ�ʹ�ã��������������");
}

function emailFormatErrorStyle() {
    $('#mail_succeed').addClass('error-ico');
    $('#mail_error').removeClass().addClass("error");
    $("#mail_error").html("�����ַ����ȷ������������");
}

// ������֤ ��֤�ֻ� �ύע��
function mobileReg() {
    var mail = $("#emailMg").val();
    var authcode = $("#authcodeMg").val();
    var email = strTrim(mail);
    var format = validateRules.isEmail(email);
    var format2 = validateRules.betweenLength(email, 0, 50);
    if (!format) {
        $("#mail_error").html("�����ַ����ȷ������������");
        return;
    } else if (!format2) {
        $("#mail_error").html("�����ַ����Ӧ��4-50���ַ�֮��");
        return;
    }

    var mobile = $("#phone1").val();
    var phonevalue = $("#phone").val();
    var mobileCode = $("#mobileCode1").val();
    if (mobile == "") {
        $('#phone1').addClass('highlight2');
        $("#phone1_error").removeClass().addClass("error").html("�������ֻ���");
        $("#phone1_error").show();
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
    }

    if (mobileCode == "") {
        $('#mobileCode1').addClass('highlight2');
        $("#mobileCodeSucMessage1").empty();
        $("#mobileCodeSucMessage1").removeClass();
        $("#mobileCode1_error").html("�����������֤��");
        $("#mobileCode1_error").removeClass().addClass("error");
        $("#mobileCode1_error").show();
        return;
    }
    if (mobile == "") {
        $('#phone1').addClass('highlight2');
        $("#phone1_error").removeClass().addClass("error").html("�������ֻ���");
        $("#phone1_error").show();
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        return;
    } else if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $("#phone1_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone1_error").removeClass().addClass("error");
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        $("#phone1_error").show();
        $('#phone1').removeClass().addClass('text highlight2');
        $("#mobileCodeDiv1").show();
        mobileFlag = false;
        return;
    }
    var state = $("#state").val();
    if (state == "unbind") {
        mobileFlag = true;
    }
    if (mobileFlag) {
        var paramList = $("#personRegForm").serialize() + "&email=" + email;
        var temp = paramList.replace("phone=" + phonevalue, "phone=" + mobile);
        var params = temp.replace("mobileCode=", "mobileCode=" + mobileCode);
        params = params.replace("authcode=", "authcode=" + authcode);
        $.ajax({
            type: "POST",
            url: "../register/sendRegEmail?r=" + Math.random() + "&" + location.search.substring(1),
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: params,
            success: function(result) {
                var obj = eval(result);
                var emailResult = obj.success;
                var key = obj.k;
                if (emailResult == 0) {
                    jdThickBoxclose();
                    jQuery.jdThickBox({
                        type: "text",
                        width: 510,
                        height: 280,
                        source: '<div class="thickbox-tip fz14">' + '<div class="icon-box">' + '<span class="succ-icon m-icon"></span>' + '<div class="item-fore">' + '<div class="ftx-02 info-succ">�˻�����ȫ���������</div>' + '</div>' + '</div>' + '<div class="msg-txt">' + 'ϵͳ������������&nbsp;<strong class="ftx-01">' + $("#emailMg").val() + '</strong>&nbsp;������һ����֤�ʼ���������¼���䣬����ʼ��е��������������֤���������2����δ�յ��ʼ���������<a href="#none" onclick="reSendEmail(\'' + $("#emailMg").val() + '\',\'' + key + '\');" class="ftx-05">���·���</a>' + '</div>' + '<div class="mt10 ftx-01"> <span id="reSendEmailSuccess"></span></div>' + '<div class="mt20">' + '<a href="javascript:;" id="emailLogin" class="btn-red">��¼����</a>'
                        // +'<a href="#none"
                        // onclick="changeEmail();"
                        // class="ftx-05 fr">���������޸�</a>'
                        + '<span class="clr"></span>' + '</div>' + '</div>',
                        title: "��ܰ��ʾ",
                        _close_val: "��",
                        _con: "opinioncon",
                        _titleOn: true
                    });

                    initEmailLoginUrl(email);
                } else {
                    $("#mobileCodeSucMessage1").removeClass().empty();
                    $("#mobileCode1_error").html(obj.info);
                    $("#mobileCode1_error").removeClass().addClass("error");
                    $("#mobileCode1_error").show();
                    $("#sendMobileCode1").removeClass().addClass("btn").removeAttr("disabled");
                }
            }
        });
    }
}
function mobileCodeError(content) {
    $("#mobileCode_error").html(content);
    $("#mobileCode_error").removeClass().addClass("error");
    $("#mobileCode_error").show();
    $("#sendMobileCode").removeClass().addClass("btn").removeAttr("disabled");
}
function mobileCodeHide() {
    $("#mobileCode_error").html("");
    $("#mobileCode_error").removeClass().addClass("error");
    $("#mobileCode_error").hide();
}
var delayTime = 120;
var delayFlag = true;
function countDown() {
    delayTime--;
    $("#sendMobileCode").attr("disabled", "disabled");
    $("#dyMobileButton").html(delayTime + '������»�ȡ');
    if (delayTime == 1) {
        delayTime = 120;
        $("#mobileCodeSucMessage").removeClass().empty();
        $("#dyMobileButton").html("��ȡ������֤��");
        $("#mobileCode_error").addClass("hide");
        $("#sendMobileCode").removeClass().addClass("btn").removeAttr("disabled");
        delayFlag = true;
    } else {
        delayFlag = false;
        setTimeout(countDown, 1000);
    }
}
var delayTime1 = 120;
var delayFlag1 = true;
function countDown1() {
    delayTime1--;
    $("#sendMobileCode1").attr("disabled", "disabled");
    $("#dyMobileButton1").html(delayTime1 + '������»�ȡ');
    if (delayTime1 == 1) {
        delayTime1 = 120;
        $("#mobileCodeSucMessage1").removeClass().empty();
        $("#dyMobileButton1").html("��ȡ������֤��");
        $("#mobileCode1_error").removeClass().empty();
        $("#mobileCode1_error").hide();
        $("#sendMobileCode1").removeClass().addClass("btn").removeAttr("disabled");
        delayFlag1 = true;
    } else {
        delayFlag1 = false;
        countDown1.timer = setTimeout(countDown1, 1000);
    }
}
countDown1.timer = '';
function strTrim(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
}

$("#regName").blur(function() {
    setTimeout(function() {
        if ($("#schoolid").val() == "") {
            $("#schoolinput").val("");
            $("#hnschool").val("-1");
            $("#hnschool").attr("sta", 0);
            $("#schoolinput_succeed").removeClass("succeed");
        } else {
            $("#hnschool").val("1");
            $("#hnschool").attr("sta", 2);
            $("#schoolinput_error").html("");
            $("#schoolinput_succeed").addClass("succeed");
        }
        $('#intelligent-school').hide().empty();
        $("#hnseli").val("-1");
    },
    200)
})

function showHideProtocol() {
    var protocolNode = $('.protocol-box');
    if (!protocolNode.is(':hidden')) {
        protocolNode.hide();
    } else {
        protocolNode.show();
    }
    return false;
}

function validateRegName() {
    var loginName = $("#regName").val();
    if (validateRules.isNull(loginName) || loginName == '') {
        $("#regName").val("");
        $("#regName").attr({
            "class": "text highlight2"
        });
        $("#regName_error").html("�������û���").show().attr({
            "class": "error"
        });
        return false;
    }
    return true;
}
$("#regist .tab li").hover(function() {
    if ($(this).hasClass("curr")) {} else {
        $(this).addClass("new");
    }
},
function() {
    if ($(this).hasClass("curr")) {} else {
        $(this).removeClass("new");
    }
})

$("#registsubmit").hover(function() {
    $(this).addClass("hover-btn")
},
function() {

    $(this).removeClass("hover-btn")
})

// �������ֻ���ý����¼�
function phoneFocus() {
    var mobile = $("#phone").val();
    if (oldMobile == mobile && mobile != "") {
        return;
    }
    $("#phone_succeed").removeClass("blank succeed");
    $('#phone').removeClass().addClass('text highlight1');
    $("#phone_error").removeClass().addClass("focus").html("�����֤���������ø��ֻ��ŵ�¼���һ�����");
    $("#phone_error").show();
    $('#phone_succeed').removeClass('error-ico');
}
//�������ֻ���ý����¼�
function phoneOtherFocus() {
    var mobile = $("#phone").val();
    if (oldMobile == mobile && mobile != "") {
        return;
    }
    $("#phone_succeed").removeClass("blank succeed");
    $('#phone').removeClass().addClass('text highlight1');
    $("#phone_error").removeClass().addClass("focus").html("�������ֻ�����");
    $("#phone_error").show();
    $('#phone_succeed').removeClass('error-ico');
}
// �������ֻ���ý����¼�
function phone1Focus() {
    var mobile1 = $("#phone1").val();
    if (oldMobile1 == mobile1 && mobile1 != "") {
        return;
    }
    $("#phone1_succeed").removeClass();
    $('#phone1').removeClass().addClass('text highlight1');
    $("#phone1_error").removeClass().addClass("focus").html("�����֤���������ø��ֻ��ŵ�¼���һ�����");
    $("#phone1_error").show();
    $('#phone1_succeed').removeClass('error-ico');
}

var oldMobile, mobileResult;
// �����̼���ֻ�
function phoneBlur() {
    var mobile = $("#phone").val();

    if (mobile == "") {
        $('#phone').removeClass().addClass('text');
        $("#phone_error").removeClass().html("");
        $("#phone_error").hide();
        $("#rebind").remove();
        $("#mobileCodeDiv").show();
        $("#phone_succeed").removeClass().addClass("");
        oldMobile = mobile;
        mobileFlags = false;
        return;
    }
    if (oldMobile == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult(mobileResult);
        return;
    }
    oldMobile = mobile;
    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $('#phone').removeClass().addClass('text highlight2');
        $("#phone_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone_error").removeClass().addClass("error");
        $("#phone_error").show();
        $("#phone_succeed").removeClass().addClass("");
        $("#rebind").remove();
        $("#mobileCodeDiv").show();
        mobileFlags = false;
        return;
    }
    $("#mobileCodeSucMessage").removeClass().empty();
    $("#mobileCode_error").html("");
    $("#mobileCode_error").hide();
    $("#state").val("");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {

        mobileResult = result.success;
        // if (mobileResult != 2) {
        // if ($("#sendMobileCode").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode").removeAttr("disabled");
        // }
        $("#sendMobileCode").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle();
        }

        if (result.success == 1) {
            mobileEngagedStyle();
        }

        if (result.success == 2) {
            mobileBindedStyle(result.ub);
            // $("#sendMobileCode").attr("disabled", "disabled");
        }
    });
}
//�����̼���ֻ�
function phoneKeyup() {
    var mobile = $("#phone").val();
    var mobileLength=mobile.length;
    if(mobileLength != 11)
    {
    	return;
    }
    if (mobile == "") {
        $('#phone').removeClass().addClass('text');
        $("#phone_error").removeClass().html("");
        $("#phone_error").hide();
        $("#rebind").remove();
        $("#mobileCodeDiv").show();
        $("#phone_succeed").removeClass().addClass("");
        oldMobile = mobile;
        mobileFlags = false;
        return;
    }
    if (oldMobile == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult(mobileResult);
        return;
    }
    oldMobile = mobile;
    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $('#phone').removeClass().addClass('text highlight2');
        $("#phone_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone_error").removeClass().addClass("error");
        $("#phone_error").show();
        $("#phone_succeed").removeClass().addClass("");
        $("#rebind").remove();
        $("#mobileCodeDiv").show();
        mobileFlags = false;
        return;
    }
    $("#mobileCodeSucMessage").removeClass().empty();
    $("#mobileCode_error").html("");
    $("#mobileCode_error").hide();
    $("#state").val("");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {

        mobileResult = result.success;
        // if (mobileResult != 2) {
        // if ($("#sendMobileCode").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode").removeAttr("disabled");
        // }
        $("#sendMobileCode").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle();
        }

        if (result.success == 1) {
            mobileEngagedStyle();
        }

        if (result.success == 2) {
            mobileBindedStyle(result.ub);
            // $("#sendMobileCode").attr("disabled", "disabled");
        }
    });
}

//�����̼���ֻ�
function phoneOtherBlur() {
    var mobile = $("#phone").val();

    if (mobile == "") {
        $('#phone').removeClass().addClass('text');
        $("#phone_error").removeClass().html("");
        $("#phone_error").hide();
        $("#phone_succeed").removeClass().addClass("");
        oldMobile = mobile;
        mobileFlags = false;
        return;
    }
    if (oldMobile == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult(mobileResult);
        return;
    }
    oldMobile = mobile;
    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $('#phone').removeClass().addClass('text highlight2');
        $("#phone_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone_error").removeClass().addClass("error");
        $("#phone_error").show();
        $("#phone_succeed").removeClass().addClass("");
        mobileFlags = false;
        return;
    }
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {

        mobileResult = result.success;
        // if (mobileResult != 2) {
        // if ($("#sendMobileCode").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode").removeAttr("disabled");
        // }
        $("#sendMobileCode").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle();
        }

        if (result.success == 1 || result.success == 2) {
        	 $('#phone').removeClass().addClass('text highlight2');
             $("#phone_error").html("���ֻ����ѱ��󶨣�������ֻ���");
             $("#phone_error").removeClass().addClass("error");
             $("#phone_error").show();
             $("#phone_succeed").removeClass().addClass("");
        	 mobileFlags = false;
        }

    });
}
//�����̼���ֻ�
function phoneOtherKeyup() {
    var mobile = $("#phone").val();
    var mobileLength=mobile.length;
    if(mobileLength != 11)
    {
    	return;
    }
    if (mobile == "") {
        $('#phone').removeClass().addClass('text');
        $("#phone_error").removeClass().html("");
        $("#phone_error").hide();
        $("#phone_succeed").removeClass().addClass("");
        oldMobile = mobile;
        mobileFlags = false;
        return;
    }
    if (oldMobile == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult(mobileResult);
        return;
    }
    oldMobile = mobile;
    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $('#phone').removeClass().addClass('text highlight2');
        $("#phone_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone_error").removeClass().addClass("error");
        $("#phone_error").show();
        $("#phone_succeed").removeClass().addClass("");
        mobileFlags = false;
        return;
    }
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {

        mobileResult = result.success;
        // if (mobileResult != 2) {
        // if ($("#sendMobileCode").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode").removeAttr("disabled");
        // }
        $("#sendMobileCode").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle();
        }

        if (result.success == 1 || result.success == 2) {
         	 $('#phone').removeClass().addClass('text highlight2');
             $("#phone_error").html("���ֻ����ѱ��󶨣�������ֻ���");
             $("#phone_error").removeClass().addClass("error");
             $("#phone_error").show();
             $("#phone_succeed").removeClass().addClass("");
       	    mobileFlags = false;
       }
    });
}
function showMobileCheckResult(result) {
    if (result == 0) {
        mobileOkStyle();
    }
    if (result == 1) {
        mobileEngagedStyle();
    }
    if (result == 2) {
        mobileBindedStyle();
    }
}

function mobileOkStyle() {
    $('#phone').removeClass().addClass("text");
    $("#phone_error").html("");
    $("#phone_error").hide();
    $("#phone_succeed").removeClass().addClass("blank succeed");
    $("#mobileCode_error").removeClass().empty();
    $("#mobileCodeDiv").show();
    $("#rebind").remove();
    $("#mobileCodeDiv").show();
    mobileFlags = true;
}

function mobileBindedStyle(ub) {
    $('#phone').removeClass().addClass('text highlight2');
    $("#phone_error").html("���ֻ����ѱ������˻��󶨣�"+ub+"���ڲ��ɸİ�");
    $("#phone_error").removeClass().addClass("error");
    $("#phone_error").show();
    $("#phone_succeed").removeClass().addClass("");
    $("#rebind").remove();
    $("#mobileCodeDiv").show();
    mobileFlags = false;
}

function mobileEngagedStyle() {
    $('#phone').removeClass().addClass('text highlight3');
    $("#phone_error").html("�ֻ�����ע�ᣬ����ע�Ὣ��ԭ�˺Ž��");
    $("#phone_error").removeClass().addClass("cue");
    $("#phone_error").show();
    $("#phone_succeed").removeClass().addClass("blank cue-ico");
    $("#rebind").remove();
    $('#dphone').after('<div class="item" id="rebind"> <span class="label">&nbsp;</span><div class="fl item-ifo item-ifo-extra"> <a href="javascript:;" onclick="unbind();" class="btn-comm"><span>����ע��</span></a> </div> </div>');
    $("#mobileCodeDiv").hide();
    mobileFlags = false;
}
function showMobileCheckResult1(result) {
    if (result == 0) {
        mobileOkStyle1();
    }
    if (result == 1) {
        mobileEngagedStyle1();
    }
    if (result == 2) {
        mobileBindedStyle1();
    }
}
function mobileOkStyle1() {
    $('#phone1').removeClass().addClass("text");
    $("#phone1_error").removeClass().addClass("success");
    $("#phone1_error").html("���ֻ��ſ���");
    $("#phone1_succeed").removeClass().addClass("blank succeed");
    $("#mobileCodeDiv1").show();
    $("#dmcode1").show();
    $("#rebind1").remove();
    mobileFlag = true;
    return;
}

function mobileBindedStyle1(ub) {
    $('#phone1').removeClass().addClass('text highlight2');
    $("#phone1_error").html("���ֻ����ѱ������˻��󶨣�"+ub+"���ڲ��ɸİ�");
    $("#phone1_error").removeClass().addClass("error");
    $("#phone1_succeed").removeClass().addClass("blank error-ico");
    $("#phone1_error").show();
    $('#phone1').removeClass("highlight1").addClass('highlight2');
    $("#sendMobileCode1").attr("disabled", "disabled");
    $("#mobileCodeDiv1").show();
    $("#rebind1").remove();
    mobileFlag = false;
    return;
}

function mobileEngagedStyle1() {
    $('#phone1').removeClass().addClass('text highlight3');
    $("#phone1_error").html("�ֻ�����ע�ᣬ����ע�Ὣ��ԭ�˺Ž��");
    $("#phone1_error").removeClass().addClass("cue");
    $("#phone1_succeed").removeClass().addClass("blank cue-ico");
    $("#phone1_error").show();
    $("#rebind1").remove();
    $('#dphone1').after('<div class="item"  id="rebind1"><span class="label">&nbsp;</span><div class="fl item-ifo"><a href="javascript:void(0);" onclick="unbind1();"  class="btn btn-comm"><span>����ע��</span></a></div></div>');
    $("#mobileCodeDiv1").hide();
    mobileFlag = false;
    return;
}
// �������ֻ�ʧȥ�����¼�
var mobileFlag = false;
var oldMobile1, mobileResult1;
function phone1Blur() {
    var mobile = $("#phone1").val();
    if (mobile == "") {
        $('#phone1').removeClass().addClass("text");
        $("#phone1_error").hide();
        $('#phone1_succeed').removeClass();
        $("#rebind1").remove();
        $("#dmcode1").show();
        $("#mobileCodeDiv1").show();
        oldMobile1 = mobile;
        mobileFlag = false;
        return;
    }
    if (oldMobile1 == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult1(mobileResult1);
        return;
    }
    oldMobile1 = mobile;

    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $("#phone1_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone1_error").removeClass().addClass("error");
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        $("#phone1_error").show();
        $('#phone1').removeClass().addClass('text highlight2');
        $("#mobileCodeDiv1").show();
        $("#rebind1").remove();
        $("#dmcode1").show();
        mobileFlag = false;
        return;
    }
    $("#state").val("");
    $("#mobileCodeSucMessage1").removeClass().empty();
    $("#mobileCode1_error").removeClass().empty();
    $("#mobileCode1_error").hide();
    $('#mobileCode1').removeClass("highlight2");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {
        // mobileResult1 = result.success;
        // if (mobileResult1 != 2) {
        // if ($("#sendMobileCode1").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode1").removeAttr("disabled");
        // }
        $("#sendMobileCode1").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle1();
        }

        if (result.success == 1) {
            mobileEngagedStyle1();
        }

        if (result.success == 2) {
            mobileBindedStyle1(result.ub);
            // $("#sendMobileCode1").attr("disabled", "disabled");
        }
    });
}
function phone1Keyup() {
    var mobile = $("#phone1").val();
    var mobileLength=mobile.length;
    if(mobileLength != 11)
    {
    	return;
    }
    if (mobile == "") {
        $('#phone1').removeClass().addClass("text");
        $("#phone1_error").hide();
        $('#phone1_succeed').removeClass();
        $("#rebind1").remove();
        $("#dmcode1").show();
        $("#mobileCodeDiv1").show();
        oldMobile1 = mobile;
        mobileFlag = false;
        return;
    }
    if (oldMobile1 == mobile && mobile != "") {
        // δ�޸��ֻ���
        // showMobileCheckResult1(mobileResult1);
        return;
    }
    oldMobile1 = mobile;

    if (validateRules.isNull(mobile) || !validateRules.isMobile(mobile)) {
        $("#phone1_error").html("�ֻ������ʽ������������ȷ���ֻ���");
        $("#phone1_error").removeClass().addClass("error");
        $("#phone1_succeed").removeClass().addClass("blank error-ico");
        $("#phone1_error").show();
        $('#phone1').removeClass().addClass('text highlight2');
        $("#mobileCodeDiv1").show();
        $("#rebind1").remove();
        $("#dmcode1").show();
        mobileFlag = false;
        return;
    }
    $("#state").val("");
    $("#mobileCodeSucMessage1").removeClass().empty();
    $("#mobileCode1_error").removeClass().empty();
    $("#mobileCode1_error").hide();
    $('#mobileCode1').removeClass("highlight2");
    // ����ֻ������Ƿ����
    $.getJSON("../validateuser/isMobileEngaged?mobile=" + mobile + "&r=" + Math.random(),
    function(result) {
        // mobileResult1 = result.success;
        // if (mobileResult1 != 2) {
        // if ($("#sendMobileCode1").attr("disabled")) {
        // return;
        // }
        // $("#sendMobileCode1").removeAttr("disabled");
        // }
        $("#sendMobileCode1").removeAttr("disabled");
        if (result.success == 0) {
            mobileOkStyle1();
        }

        if (result.success == 1) {
            mobileEngagedStyle1();
        }

        if (result.success == 2) {
            mobileBindedStyle1(result.ub);
            // $("#sendMobileCode1").attr("disabled", "disabled");
        }
    });
}
// �����̶�����֤���ý����¼�
function mobileCodeFocus() {
    $('#mobileCode').removeClass().addClass('text text-1 highlight1');
    $("#mobileCode_error").hide();
}
// �����̶�����֤����ʧȥ�¼�
function mobileCodeBlur() {
    $('#mobileCode').removeClass().addClass("text text-1");
    $("#mobileCode_error").hide();
}
// �����̶�����֤���ý����¼�
function mobileCode1Focus() {
    $('#mobileCode1').removeClass().addClass('text text-1 highlight1');
    $("#mobileCode1_error").hide();
}
// �����̶�����֤����ʧȥ�¼�
function mobileCode1Blur() {
    $('#mobileCode1').removeClass().addClass("text text-1");
    $("#mobileCode1_error").hide();
    $('#mobileCode1_succeed').removeClass('error-ico');
}
// ���ť�¼�
function unbind() {
    $("#state").val("unbind");
    $("#mobileCodeDiv").show();
    $("#rebind").remove();
    // sendmCode();
    sendMobileCode();
}
// �����̽��ť�¼�
function unbind1() {
    $("#state").val("unbind");
    $("#mobileCodeDiv1").show();
    $("#rebind1").remove();
    sendMobileCode1();
}
// �û�Э��
$(function() {
    $('#protocol').click(function() {
        jQuery.jdThickBox({
            type: "text",
            title: "�����û�ע��Э��",
            width: 922,
            height: 450,
            source: "<div class=\" regist-2013\">" + "<div class=\"regist-bor\">" + "<div class=\"mc\">" + "<div id=\"protocol-con\">" + " <h4>�����û�ע��Э��</h4>" +

            "    <p>" + "        ��Э�������뾩����վ�����\"��վ\"����ַ��www.jd.com�������ߣ����¼��Ϊ\"����\"��֮��;�����վ����������������������Լ��������ϸ�Ķ���ע��Э�飬�����\"ͬ�Ⲣ����\"��ť�󣬱�Э�鼴���ɶ�˫����Լ�����ķ����ļ���</p>" + "    <h5> ��1�� ��վ���������ȷ�Ϻͽ���</h5>" +

            "    <p>" + "        <strong>1.1</strong>��վ�ĸ�����ӷ��������Ȩ������Ȩ�龩�����С��û�ͬ������ע��Э��������ע����򣬲��ܳ�Ϊ��վ����ʽ�û����û�ȷ�ϣ���Э�������Ǵ���˫��Ȩ���������Լ��ʼ����Ч����������ǿ���Թ涨��˫�������ر�Լ���ģ�����涨��" + "    </p>" +

            "    <p>" + "        <strong>1.2</strong>�û����ͬ�ⱾЭ��ģ�����Ϊ�û�ȷ���Լ��������ܱ�վ�����µ��������Ӧ��Ȩ����������Ϊ�������ܹ������е��������Ρ�</p>" +

            "    <p>" + "        <strong>1.3</strong>�������18�������£���ֻ���ڸ�ĸ��໤�˵ļ໤�����²���ʹ�ñ�վ��</p>" +

            "    <p>" + "        <strong>1.4</strong>�����������л����񹲺͹���½������ʩ��֮��������ķ�Χ�ڶ��Ծ����ܾ����񡢹ر��û��˻��������༭���ݻ�ȡ��������Ȩ����</p>" + "    <h5> ��2�� ��վ����</h5>" +

            "    <p>" + "        <strong>2.1</strong>����ͨ������������Ϊ�û��ṩ��������Ϣ�ȷ����û�����ȫͬ�ⱾЭ�鼰��վ�涨������£�����Ȩʹ�ñ�վ����ط���</p>" +

            "    <p>" + "        <strong>2.2</strong>�û���������׼�������豸�ͳе����¿�֧����1�������豸�������������ڵ��Ի������������նˡ����ƽ�����������ر�������װ�ã���2��������֧���������������������ѡ������豸���÷ѡ��ֻ������ѵȡ�" + "    </p>" + "    <h5> ��3�� �û���Ϣ</h5>" +

            "    <p>" + "        <strong>3.1</strong>�û�Ӧ���г�����վ�ṩע�����ϣ��û�ͬ�����ṩ��ע��������ʵ��׼ȷ���������Ϸ���Ч���û�ע���������б䶯�ģ�Ӧ��ʱ������ע�����ϡ�����û��ṩ��ע�����ϲ��Ϸ�������ʵ����׼ȷ�����꾡�ģ��û���е�����������Ӧ���μ���������Ҿ���������ֹ�û�ʹ�þ�����������Ȩ����" + "    </p>" +

            "    <p>" + "        <strong>3.2</strong>�û��ڱ�վ����������µ�����Ȼʱ���漰�û���ʵ����/���ơ�ͨ�ŵ�ַ����ϵ�绰�������������˽��Ϣ�ģ���վ�������ϸ��ܣ����ǵõ��û�����Ȩ�������й涨����վ�����������¶�û���˽��Ϣ��" + "    </p>" +

            "    <p>" + "        <strong>3.3</strong>�û�ע��ɹ��󣬽������û�����������˻���Ϣ�������Ը��ݱ�վ�涨�ı��������롣�û�Ӧ��������ı��桢ʹ�����û��������롣�û��������κηǷ�ʹ���û��˺Ż���ڰ�ȫ©���������������֪ͨ��վ���򹫰����ر�����" + "    </p>" +

            "    <p>" + "        <strong>3.4</strong><strong>�û�ͬ�⣬����ӵ��ͨ���ʼ������ŵ绰����ʽ�����ڱ�վע�ᡢ�����û����ջ��˷��Ͷ�����Ϣ��������ȸ�֪��Ϣ��Ȩ����</strong></p>" +

            "    <p>" + "        <strong>3.5</strong><strong>�û����ý��ڱ�վע���õ��˻��������ʹ�ã������û�Ӧ�е��ɴ˲�����ȫ�����Σ�����ʵ��ʹ���˳е��������Ρ�</strong></p>" +

            "    <p>" + "        <strong>3.6</strong><strong>�û�ͬ�⣬������Ȩʹ���û���ע����Ϣ���û������������Ϣ����¼�����û���ע���˻�������֤�ݱ�ȫ�������������ڹ�֤����֤�ȡ�</strong></p>" + "    <h5> ��4�� �û�������������</h5>" +

            "    <p> ��Э�����ݹ�����ط��ɷ�������ƶ����û�ͬ���ϸ�������������</p>" +

            "    <p>" + "        <strong>��1��</strong>���ô���򷢱�ɿ�����ܡ��ƻ��ܷ��ͷ��ɡ���������ʵʩ�����ۣ�ɿ���߸�������Ȩ���Ʒ���������ƶȵ����ۣ�ɿ�����ѹ��ҡ��ƻ�����ͳһ�ĵ����ۣ�ɿ�������ޡ��������ӡ��ƻ������Ž�����ۣ�" + "    </p>" +

            "    <p>" + "        <strong>��2��</strong>���й���½���⴫��������Ϣʱ��������й��йط��棻</p>" +

            "    <p>" + "        <strong>��3��</strong>�������ñ�վ����ϴǮ����ȡ��ҵ���ܡ���ȡ������Ϣ��Υ��������" + "    </p>" +

            "    <p>" + "        <strong>��4��</strong>���ø��ű�վ��������ת���������뱾վ�����Ҽ������Ϣϵͳ��</p>" +

            "    <p>" + "        <strong>��5��</strong>���ô���򷢱��κ�Υ������ġ�ɧ���Եġ��������˵ġ������Եġ������Եġ��˺��Եġ�ӹ�׵ģ�����ġ��������ĵ���Ϣ���ϣ�</p>" +

            "    <p>" + "        <strong>��6��</strong>���ô���򷢱��𺦹�����ṫ��������漰���Ұ�ȫ����Ϣ���ϻ����ۣ�</p>" +

            "    <p>" + "        <strong>��7��</strong>���ý������˴��±�������ֹ����Ϊ��</p>" +

            "    <p>" + "        <strong>��8��</strong>���������ڱ�վע����˻�����Ĳ���Ծ�Ӫ���</p>" +

            "    <p>" + "        <strong>��9��</strong>���÷����κ��ַ���������Ȩ���̱�Ȩ��֪ʶ��Ȩ��Ϸ�Ȩ�������ݣ�</p>" +

            "    <p>" + "        �û�Ӧ��ʱ��ע�����ر�վ��ʱ�������޸ĵĸ���Ϸ�����涨��</p>" +

            "    <p>" + "        <strong>��վ����ɾ��վ�ڸ��಻���Ϸ������߻���ʵ����Ϣ���ݶ�����֪ͨ�û���Ȩ����</strong></p>" +

            "    <p>" + "        <strong>���û�δ�������Ϲ涨�ģ���վ��Ȩ���������жϲ���ȡ��ͣ��ر��û��ʺŵȴ�ʩ��</strong>�û�����Լ������ϵ����ۺ���Ϊ�е��������Ρ�</p>" + "    <h5> ��5�� ��Ʒ��Ϣ</h5>" +

            "    <p>" + "        ��վ�ϵ���Ʒ�۸��������Ƿ��л�����Ʒ��Ϣ��ʱ���п��ܷ����䶯����վ�����ر�֪ͨ��������վ����Ʒ��Ϣ�����������Ӵ���Ȼ��վ�ᾡ���Ŭ����֤���������Ʒ��Ϣ��׼ȷ�ԣ�������������֪�Ļ������������صȿ͹�ԭ����ڣ���վ��ҳ��ʾ����Ϣ���ܻ���һ�����ͺ��Ի����Դ�������֪Ϥ����⣻������ӭ����������������������һ���Ľ�����</p>" +

            "    <p> Ϊ������������Ʒ�ͷ�����Ϊ\"��Ʒ\"��\"����\"��</p>" + "    <h5> ��6�� ����</h5>" +

            "    <p>" + "        <strong>6.1</strong>�����¶���ʱ��������ϸȷ��������Ʒ�����ơ��۸��������ͺš���񡢳ߴ硢��ϵ��ַ���绰���ջ��˵���Ϣ��<span>�ջ������û����˲�һ�µģ��ջ��˵���Ϊ����˼��ʾ��Ϊ�û�����Ϊ����˼��ʾ���û�Ӧ���ջ��˵���Ϊ����˼��ʾ�ķ��ɺ���е��������Ρ�</span>" + "    </p>" +

            "    <p>" + "        <strong>6.2</strong><strong>����������ǿ���Թ涨�⣬˫��Լ�����£���վ�����۷�չʾ����Ʒ�ͼ۸����Ϣ�����ǽ�����Ϣ�ķ��������µ�ʱ����д��ϣ���������Ʒ�������ۿ֧����ʽ���ջ��ˡ���ϵ��ʽ���ջ���ַ�����ݣ�ϵͳ���ɵĶ�����Ϣ�Ǽ������Ϣϵͳ��������д�������Զ����ɵ����ݣ������������۷������Ľ����������۷��յ����Ķ�����Ϣ��ֻ�������۷������ڶ����ж�������Ʒ�Ӳֿ�ʵ��ֱ����������ʱ�� ����Ʒ����Ϊ��־��������Ϊ�������۷�֮���ʵ��ֱ��������������Ʒ�����˽��׹�ϵ���������һ�ݶ����ﶩ���˶�����Ʒ�������۷�ֻ���������˲�����Ʒʱ���������۷�֮�����ʵ��ֱ��������������Ʒ�����˽��׹�ϵ��ֻ�������۷�ʵ��ֱ�����������˶����ж�����������Ʒʱ���������۷�֮��Ͷ����и�������ʵ��ֱ��������������Ʒ�ų������׹�ϵ����������ʱ��¼���ڱ�վע����˻�����ѯ���Ķ���״̬��</strong>" + "    </p>" +

            "    <p>" + "        <strong>6.3</strong><strong>�����г��仯�������Ժ�����ҵŬ�����Կ��Ƶ����ص�Ӱ�죬��վ�޷���֤���ύ�Ķ�����Ϣ��ϣ���������Ʒ�����л��������⹺�����Ʒ������ȱ��������Ȩȡ��������</strong>" + "    </p>" + "    <h5> ��7�� ����</h5>" +

            "    <p>" + "        <strong>7.1</strong>���۷��������Ʒ������͵�����ָ�����ջ���ַ�������ڱ�վ���г����ͻ�ʱ��Ϊ�ο�ʱ�䣬�ο�ʱ��ļ����Ǹ��ݿ��״���������Ĵ�����̺��ͻ�ʱ�䡢�ͻ��ص�Ļ����Ϲ��Ƶó��ġ�</p>" + "" + "    <p>" + "        <strong>7.2</strong>�����������ɶ����ӳٻ��޷����͵ȣ����۷����е��ӳ����͵����Σ�</p>" +

            "    <p>" + "        <strong>��1��</strong>�û��ṩ����Ϣ���󡢵�ַ����ϸ��ԭ���µģ�" + "    </p>" +

            "    <p>" + "        <strong>��2��</strong>�����ʹ������ǩ�գ������޷����ͻ��ӳ����͵ģ�</p>" +

            "    <p>" + "        <strong>��3��</strong>���Ʊ�����ص��µģ�</p>" +

            "    <p>" + "        <strong>��4��</strong>���ɿ������ص��µģ����磺��Ȼ�ֺ�����ͨ���ϡ�ͻ��ս���ȡ�</p>" + "    <h5> ��8�� ����Ȩ��֪ʶ��Ȩ����</h5>" +

            "    <p>" + "        <strong>8.1</strong><strong>�û�һ�����ܱ�Э�飬���������û������������κ�ʱ����ڱ�վ������κ���ʽ����Ϣ���ݣ������������ڿͻ����ۡ��ͻ���ѯ�����໰�����µ���Ϣ���ݣ��ĲƲ���Ȩ�����κο�ת�õ�Ȩ����������Ȩ�Ʋ�Ȩ�������������ڣ�����Ȩ������Ȩ������Ȩ��չ��Ȩ������Ȩ����ӳȨ���㲥Ȩ����Ϣ���紫��Ȩ������Ȩ���ı�Ȩ������Ȩ�����Ȩ�Լ�Ӧ��������Ȩ�����е�������ת��Ȩ������ȫ�������Ҳ��ɳ�����ת�ø��������У��û�ͬ�⾩����Ȩ���κ�������Ȩ�������������ϡ�</strong>" + "    </p>" +

            "    <p>" + "        <strong>8.2</strong><strong>��Э���Ѿ����ɡ��л����񹲺͹�����Ȩ�����ڶ�ʮ�����������������2011�������Ȩ��ȷ��������ط��ɹ涨�������Ʋ�Ȩ��Ȩ��ת������Э�飬��Ч�������û��ھ�����վ�Ϸ������κ�������Ȩ����������Ʒ���ݣ����۸õ������γ��ڱ�Э�鶩��ǰ���Ǳ�Э�鶩����</strong>" + "    </p>" +

            "    <p>" + "        <strong>8.3</strong><strong>�û�ͬ�Ⲣ�ѳ���˽ⱾЭ��������ŵ�����ѷ����ڱ�վ����Ϣ�����κ���ʽ��������Ȩ�����������κη�ʽʹ�ã��������������ڸ�����վ��ý����ʹ�ã���</strong></p>" +

            "    <p>" + "        <strong>8.4</strong><strong>�����Ǳ�վ��������,ӵ�д���վ���ݼ���Դ������Ȩ�ȺϷ�Ȩ��,�ܹ��ҷ��ɱ���,��Ȩ��ʱ�ضԱ�Э�鼰��վ�����ݽ����޸ģ����ڱ�վ��������������֪ͨ�û����ڷ������������޶ȷ�Χ�ڣ������Ա�Э�鼰��վ����ӵ�н���Ȩ��</strong>" + "    </p>" +

            "    <p>" + "        <strong>8.5</strong><strong>����������ǿ���Թ涨�⣬δ��������ȷ���ر��������,�κε�λ����˲������κη�ʽ�Ƿ���ȫ���򲿷ָ��ơ�ת�ء����á����ӡ�ץȡ����������ʽʹ�ñ�վ����Ϣ���ݣ����򣬾�����Ȩ׷���䷨�����Ρ�</strong>" + "    </p>" + "    <p>" + "        <strong>8.6</strong>��վ�����ǵ�������Ϣ���������֡�ͼ����ʶ����ťͼ�ꡢͼ�������ļ�Ƭ�Ρ��������ء����ݱ༭������������Ǿ������������ṩ�ߵĲƲ������й��͹��ʰ�Ȩ���ı�������վ���������ݵĻ���Ǿ����������Ʋ������й��͹��ʰ�Ȩ���ı�������վ������������Ǿ������������˾���������Ӧ�̵ĲƲ������й��͹��ʰ�Ȩ���ı�����" + "    </p>" + "    <h5> ��9�� �������Ƽ�����ŵ����</h5>" + "    <p>" + "        <strong>����������ȷ������˵��,��վ�����������Ļ���������ʽͨ����վ�ṩ������ȫ����Ϣ�����ݡ����ϡ���Ʒ������������ͷ��񣬾�����\"����״\"��\"������\"�Ļ������ṩ�ġ�</strong></p>" +

            "    <p>" + "        <strong>����������ȷ������˵��,�������Ա�վ����Ӫ��������ڱ���վ�ϵ���Ϣ�����ݡ����ϡ���Ʒ�������������������κ���ʽ�ġ���ʾ��Ĭʾ�������򵣱��������л����񹲺͹��������й涨�����⣩��</strong>" + "    </p>" + "    <p>" + "        <strong>������������վ�������Ļ���������ʽͨ����վ�ṩ������ȫ����Ϣ�����ݡ����ϡ���Ʒ������������ͷ������������ӱ�վ�����ĵ����ż�����Ϣû�в����������к��ɷ֡�</strong></p>" + "    <p>" + "        <strong>���򲻿ɿ�����������վ�޷����Ƶ�ԭ��ʹ��վ����ϵͳ�������޷�����ʹ�õ������Ͻ����޷���ɻ�ʧ�йص���Ϣ����¼�ȣ����������ؾ���Э�������ƺ����ˡ�</strong></p>" + "    <h5> ��10�� Э����¼��û���ע����</h5>" + "    ���ݹ��ҷ��ɷ���仯����վ��Ӫ��Ҫ��������Ȩ�Ա�Э�����ʱ�ؽ����޸ģ��޸ĺ��Э��һ���������ڱ�վ�ϼ���Ч��������ԭ����Э�顣�û�����ʱ��¼��������Э�飻<strong><em>�û�������ʱ��ע���Ķ����°��Э�鼰��վ���档���û���ͬ����º��Э�飬������Ӧ����ֹͣ���ܾ�����վ���ݱ�Э���ṩ�ķ������û�����ʹ�ñ���վ�ṩ�ķ���ģ�����Ϊͬ����º��Э�顣������������ʹ�ñ�վ֮ǰ�Ķ���Э�鼰��վ�Ĺ��档</em></strong>" + "    �����Э�����κ�һ������Ϊ��ֹ����Ч�����κ����ɲ���ִ�У�����Ӧ��Ϊ�ɷֵ��Ҳ���Ӱ���κ������������Ч�ԺͿ�ִ���ԡ�" + "    <h5> ��11�� ���ɹ�Ͻ������</h5>" + "    ��Э��Ķ�����ִ�кͽ��ͼ�����Ľ����Ӧ�������л����񹲺͹���½��������֮��Ч���ɣ������������ͻ�����򣩡� �緢����Э��������֮������ִ�ʱ������Щ�����ȫ�����ɹ涨���½��ͣ���������Ч���������Ч��" + "    ���Լ���ͱ�Э�����ݻ���ִ�з����κ����飬˫��Ӧ�����Ѻ�Э�̽����Э�̲���ʱ���κ�һ���������й�ϽȨ���л����񹲺͹���½������Ժ�������ϡ�" + "    <h5> ��12�� ���� </h5>" + "    <p>" + "        <strong>12.1</strong>������վ��������ָ����������������ɻ򱸰��ľ�����վ��Ӫ���塣</p>" +

            "    <p>" + "        <strong>12.2</strong>���������û��������ߵĺϷ�Ȩ������Э�鼰����վ�Ϸ����ĸ�������������������ݣ�����Ϊ�˸��õġ����ӱ�����Ϊ�û����������ṩ���񡣱�վ��ӭ�û����������������ͽ��飬���������Ľ��ܲ���ʱ�޸ı�Э�鼰��վ�ϵĸ������" + "    </p>" +

            "    <p>" + "        <strong>12.3</strong><span>��Э���������Ժ��塢�Ӵ֡��»��ߡ�б��ȷ�ʽ������ʶ��������û������Ķ���</span></p>" + "    <p>" + "        <strong>12.4</strong><span>�������Э���·���\"ͬ�Ⲣ����\"��ť����Ϊ����ȫ���ܱ�Э�飬�ڵ��֮ǰ�����ٴ�ȷ����֪Ϥ����ȫ��ⱾЭ���ȫ�����ݡ�</span></p>" + "</div>" + "      <div class=\"btnt\">" + "         <input  class=\"btn-img\"  type=\''button\" value='ͬ�Ⲣ����' onclick='protocolReg();'/>" + "     </div>" + "</div>" + "</div>" + "</div>",
            _autoReposi: true
        });
    });
});