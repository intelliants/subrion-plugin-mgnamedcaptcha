<script type="text/javascript">
var namedButtonCaptcha = {
    onButtonHovered: function(buttonElement)
    {
        buttonElement.className = 'nbc-button nbc-button-hovered';
    },
    onButtonBlurred: function(buttonElement)
    {
        if (buttonElement.prevClass)
        {
            buttonElement.className = buttonElement.prevClass;
        }
        else
        {
            buttonElement.className = 'nbc-button';
        }
    },
    onButtonClicked: function(buttonElement)
    {
        if (this.getElement('ipt-' + buttonElement.id))
        {
            this.getElement('nbc-container').removeChild(this.getElement('ipt-' + buttonElement.id));
            buttonElement.className = 'nbc-button';
        }
        else
        {
            var el = document.createElement('input');
            el.id = 'ipt-' + buttonElement.id;
            el.name = 'nbc[]';
            el.value = buttonElement.id.substring(3);
            el.type = 'hidden';
            this.getElement('nbc-container').appendChild(el);
            buttonElement.className = 'nbc-button nbc-button-selected';
        }

        buttonElement.prevClass = buttonElement.className;
    },
    getElement: function (id)
    {
        return document.getElementById(id);
    }
};
</script>

<style type="text/css">
    .nbc-button {cursor:pointer;border:#ccc solid 3px;padding:4px;float:left;width: 30px;margin-left:2px;text-align:center}
    .nbc-button-hovered {border-color: #CE1F21}
    .nbc-button-selected {border-color: #3342E4}
</style>

<div><?php echo $message; ?></div>

<?php foreach ($buttons as $buttonValue => $buttonLabel): ?>
<div id="nbc<?php echo $buttonValue; ?>" onmouseover="namedButtonCaptcha.onButtonHovered(this)"
     onmouseout="namedButtonCaptcha.onButtonBlurred(this)"
     onclick="namedButtonCaptcha.onButtonClicked(this)"
     class="nbc-button"><?php echo $buttonLabel; ?></div>
<?php endforeach; ?>

<div id="nbc-container"></div>