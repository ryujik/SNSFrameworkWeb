$(function () {
	$("#btnConnectFB").click(pressed_btnConnectFB);
	$("#btnDisconnectFB").click(pressed_btnDisconnectFB);
	$("#btnConnectTW").click(pressed_btnConnectTW);
	$("#btnDisconnectTW").click(pressed_btnDisconnectTW);
});

function pressed_btnConnectFB() {
	window.location.href = "process/loginFB.php";
}

function pressed_btnDisconnectFB() {
	window.location.href = "process/logoutFB.php";
}

function pressed_btnConnectTW() {
	window.location.href = "process/loginTW.php";
}

function pressed_btnDisconnectTW() {
	window.location.href = "process/logoutTW.php";
}