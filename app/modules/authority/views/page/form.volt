<div class="row">
    <div class="navbar navbar-light bg-light"> {{ linkTo(['/authority/page/','Go to the List of Pages','class':'nav-item nav-link']) }}</div>
</div>
<div class="row">
    <div class="col-md-6 card">
        <div class="card-body">
{{ form('authority/page/create', 'method': 'post') }}
            <h3 class="card-title">Page Editing </h3>
{% set group=0 %}
{% for element in form %}
    {% if element.getUserOption('group')=='true' %}
{% if group==0 %}
<div class="control-group form-group">
    <div class="controls">
    <label class="control-label d-block" for="{{ element.getName() }}">{{ element.getUserOption('h3') }}</label>
    {% endif %}
        {% set group=1 %}
    {{ element }}
        <span>{{ element.getLabel() }}</span>

    {% else %}
        {% if group==1 %}
            </div>
            </div>
            {%  set group=0 %}
            {% endif %}
        {% if element.getUserOption('no_style') %}
            {{ element }}
            {% else %}
    <div class="control-group">
        {{ element.label(["class": "control-label"]) }}
        <div class="controls">
            {{ element }}
        </div>
    </div>
        {% endif %}
    {% endif %}
{% endfor %}
            <div class="clearfix my-2">
{{ submit_button('Save','class': 'btn btn-outline-primary float-left') }}
            </div>
{{ end_form() }}
        </div>
    </div>
</div>