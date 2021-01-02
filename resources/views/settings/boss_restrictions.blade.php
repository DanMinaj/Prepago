</div>
<div class="admin2">

    <h1>Boss Restrictions Settings</h1>
    <div class="cl">&nbsp;</div>

    @include('includes.notifications')

    <form class="well" action="{!!URL::to('settings/boss_restrictions')!!}" method="POST">
        <fieldset>

            @if ($bossLevel == 0)
                <div class="control-group">
                    <label class="control-label" for="number_agents">Number Of Agents:</label>
                    <div class="controls">
                        <input type="text" class="input-xsmall" id="number_agents" name="number_agents" value="{!! $settings ? $settings->number_agents : '' !!}">
                    </div>
                </div>
            @endif

            <div class="control-group">
                <label class="control-label" for="number_distributors">Number Of Distributors Per Agent:</label>
                <div class="controls">
                    <input type="text" class="input-xsmall" id="number_distributors" name="number_distributors" value="{!! $settings ? $settings->number_distributors : '' !!}">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="number_operators">Number Of Operators Per Distributor:</label>
                <div class="controls">
                    <input type="text" class="input-xsmall" id="number_operators" name="number_operators" value="{!! $settings ? $settings->number_operators : '' !!}">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="number_schemes_per_level">Number Of Schemes Per Level:</label>
                <div class="controls">
                    <input type="text" class="input-xsmall" id="number_schemes_per_level" name="number_schemes_per_level" value="{!! $settings ? $settings->number_schemes_per_level : '' !!}">
                </div>
            </div>

            <div class="form-actions">
                <input type="submit" value="Submit" class="btn btn-primary" />
            </div>
        </fieldset>
    </form>

</div>