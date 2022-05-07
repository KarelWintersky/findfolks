<style>
    .center {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        border: 10px solid darkgray;
        padding: 10px;
    }
    .large *{
        font-size: x-large;
    }
    .text--small {
        font-size: x-small;
    }
</style>
<div class="center large">
    <form action="/admin" method="POST">
        <table>
            {*<tr>
                <td>Login &nbsp;&nbsp;&nbsp;</td>
                <td><input type="text" tabindex="1" placeholder="Login" value="admin" name="auth:login">      </td>
            </tr>*}
            <input type="hidden" tabindex="1" value="admin" name="auth:login">
            <tr>
                <td>Password&nbsp;&nbsp;&nbsp;</td>
                <td><input type="password" tabindex="2" placeholder="Password" value="" name="auth:password"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center">
                    <br>
                    <input type="submit" tabindex="3" value="Login...">
                </td>
            </tr>
            <tr>
                <td  class="text--small" colspan="2">
                    <br>
                    Не пытайтесь подобрать пароль. В нём 28 букв и цифр.
                </td>
            </tr>
        </table>
    </form>

</div>
