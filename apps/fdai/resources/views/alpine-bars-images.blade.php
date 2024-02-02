<div class="container pt-2 mx-auto" x-data="model">
    @include('search-input')
    <div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-list"
         class="flex-wrap justify-center" >
        <template x-for="item in model.{{$searchId ?? $table ?? "no-search-id-provided"}}" :key="item">
            <a :href="`${item.url}`"
               :title="`${item.tooltip}`" style="
                    -webkit-box-shadow: unset;
                    box-shadow: unset;
                    transition: unset;
                ">
                <div class="bar-chart-button" style="width:98%; display:block; height: 60px; margin-bottom: 10px;">
                    <div
                        :style="`width: ${item.width}%;
                        max-height: 60px;
                        margin: 3px;
                        background-color: ${item.color};
                        border: 0 solid transparent;
                        border-radius:60px;
                        display:inline-block;
                        font-size:16px;
                        text-align:left;
                        text-decoration:none;
                        -webkit-text-size-adjust:none;
                        mso-hide:all;`">
                        <table border="0" cellspacing="0" cellpadding="0" style="
                            padding: 0;
                            border-collapse: collapse; border-spacing: 0;
                            width: 100%;
                            border: 0 solid transparent;
                            margin: 0;
                            height: 60px;
                            display: block;
                        ">
                            <tbody style="height: 60px;
                            display: block; border: 0 solid transparent; ">
                            <tr style="
                                color:white;
                                height: 60px;
                            display: block;
                                border: 0 solid transparent;
                                ">
                                <td align="center" height="60" width="60" style="
                                        padding: 0;
                                        object-fit: contain;
                                        width: 60px;
                                        border-radius: 60px;
                                        border: 0 solid transparent;
                                        height: 60px;
                                        display: inline-block;
                                        vertical-align: top;
                                    ">
                                    <div :style="`
                                          width: 60px;
                                          height: 60px;
                                          border-radius: 50%;
                                        border: 6px solid ${item.color};
                                        box-shadow: inset 0 0 0 10px white;
                                        box-sizing: border-box; /* Include padding and border in element's width and height */
                                      `">
                                        <img height="40" width="40" class="image" :src="`${item.avatar}`" :alt="`${item.title}`" style="
                                                        border: 4px solid white;
                                                        background-color: white;
                                                        width: 40px;
                                                        height: 40px;
                                                        border-radius: 40px;
                                                        box-sizing: content-box;
                                                        object-fit: scale-down;
                                                    ">
                                    </div>
                                </td>
                                <td style="
                                        padding: 0;
                                        color: white;
                                        width: 38%;
                                        border: 0 solid transparent;
                                        height: 58px;
                                        display: inline-block;
                                    ">
                                    <div style="
                                        display: table;
                                        height: 58px;
                                        overflow: hidden;
                                        width: 100%;
                                        ">
                                        <div style="display: table-cell; vertical-align: middle; height: 58px; ">
                                            <div style="padding-left: 3px;" x-text="item.title"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="
                                        position: relative;
                                        text-align: right;
                                        padding: 0;
                                        margin: 0 3px 0 0;
                                        width: 32%;
                                        color: white;
                                        border: 0 solid transparent;
                                        height: 58px;
                                        display: inline-block;
                                        float: right;
                                    ">
                                    <div style="
                                        display: table;
                                        height: 58px;
                                        overflow: hidden;
                                        width: 100%;
                                        ">
                                        <div style="display: table-cell; vertical-align: middle;">
                                            <div style="padding-right: 6px;"  x-text="item.badge_text">
                                            </div>
                                        </div>
                                    </div>
                                </td>


                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </a>
        </template>
    </div>
</div>
@include('not-found-box')
