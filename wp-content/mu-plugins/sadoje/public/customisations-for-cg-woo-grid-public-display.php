<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

?>
<!-- Vue -->
<div id="appTest">
    <counter></counter>
</div>
<!-- / Vue  -->

<div class="cdg_box">
    <h3 class="cdg_title">Elige el tipo de tu estampación para hacer tu presupuesto</h3>
    <table class="cdg_table">
        <tbody>
            <tr>
                <th><span class="cdg_alert">1</span> Tipo de estampación</th>
                <td>
                    <div>
                        <b>Tipo de estampación delantera</b><br>
                        <select id="estampado_delantero" name="estampado_delantero">
                            <option value="">Sin estampación</option>
                            <option value="Serigrafía 1 color">Serigrafía 1 color</option>
                            <option value="Serigrafía 2 colores">Serigrafía 2 colores</option>
                            <option value="Serigrafía 3 colores">Serigrafía 3 colores</option>
                            <option value="Serigrafía 4 colores">Serigrafía 4 colores</option>
                            <option value="Bordado">Bordado</option>
                            <option value="Digital todo color">Digital todo color</option>						
                        </select>
                    </div>
                    <div>
                        <b>Tipo de estampación trasera</b><br>
                        <select id="estampado_trasero" name="estampado_trasero">
                            <option value="">Sin estampación</option>
                            <option value="Serigrafía 1 color">Serigrafía 1 color</option>
                            <option value="Serigrafía 2 colores">Serigrafía 2 colores</option>
                            <option value="Serigrafía 3 colores">Serigrafía 3 colores</option>
                            <option value="Serigrafía 4 colores">Serigrafía 4 colores</option>
                            <option value="Bordado">Bordado</option>
                            <option value="Digital todo color">Digital todo color</option>						
                        </select>
                    </div>
                    <div style="clear: both;float: none"></div>
                </td>
            </tr>
            <tr>
                <th><span class="cdg_alert">2</span> Área de estampación</th>
                <td>
                    <div class="disabled">
                        <b>Área de estampación delantera</b><br>
                        <select id="tamano_delantero" name="tamano_delantero" disabled="">
                            <option value="">Primero seleccione tipo de estampación</option>
                        </select>
                    </div>
                    <div class="disabled">
                        <b>Área de estampación trasera</b><br>
                        <select id="tamano_trasero" name="tamano_trasero" disabled="">
                            <option value="">Primero seleccione tipo de estampación</option>
                        </select>
                    </div>
                    <div style="clear: both;float: none"></div>
                </td>
            </tr>
            <tr>
                <th><span class="cdg_alert">3</span> Embolsado</th>
                <td>
                    <span style="display: inline-block;padding-right: 15px;"><input type="radio" name="embolsado" value="Si" class="pp_embolsado" style="width: 15px;height: 15px;vertical-align: sub;"> Si</span>
                    <span style="display: inline-block;padding-right: 15px;"><input type="radio" name="embolsado" value="No" class="pp_embolsado" checked="" style="width: 15px;height: 15px;vertical-align: sub;"> No</span>
                    <small style="display: inline-block;padding-right: 15px;"><li>Si elijes la opción sin embolsado, te enviaremos tus prendas dobladas por la mitad y ordenadas por tallas.</li></small>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
        <ul>
            <li>Puedes pedir prendas de diferentes colores y por supuesto tallas.</li>
            <li>No es necesario que pidas las camisetas por múltiplos, puedes pedirlas como tu quieras.</li>
            <li>La sublimación posicional solo se puede aplicar en prendas con colores claros.</li>
            <li>Durante el proceso de pedido podrás adjuntar el logo o el diseño o lo que tu quieras, también indicarnos los detalles del diseño o cualquier cosa que nos quieras decir, de cualquier manera no te preocupes por nada, ya que, antes de estampar tus prendas, recibirás una simulación digital para que la revises, en ese momento podrás indicarnos las modificaciones que desees, tamaño, color, posición, etc, las haremos si es el caso y te mandaremos la simulación modificada, una vez tengamos tu confirmación, tu pedido pasará al taller de estampación, nunca antes.</li>
            <li>Entrega: Sin impresión, entre 2 y 5 días, con impresión máximo 15 días laborables, podemos pactar un plazo de entrega personalizado previo acuerdo, indícalo al hacer pedido y te llamaremos.</li>
        </ul>
    </div>
</div>