Vue.directive('datepicker', {
    params: ['button-only'],
    bind: function () {
        if (this.params.buttonOnly) {
            $(this.el).datepicker({
                dateFormat: "dd/mm/yy",
                buttonImage: '/images/icons/calendar.png',
                buttonImageOnly: true,
                showOn: 'both'
            });
        } else {
            $(this.el).datepicker({
                dateFormat: "dd/mm/yy",
            });
        }
    }
});

Vue.directive('modal', {
    twoWay: true,
    update: function () {
        var self = this;

        $(this.el).click(function () {
            self.set(false);
        });

        $(this.el).children().click(function (e) {
            e.stopPropagation();
        });
    }
});
