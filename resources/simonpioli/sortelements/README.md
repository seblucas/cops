sort
---

The sort plugin is a very basic low-level element sorting function that allows you to sort DOM elements with a custom comparator (similar to `Array.prototype.sort`).

Assuming the following markup:

    <ul>
        <li>Banana</li>
        <li>Carrot</li>
        <li>Apple</li>
    </ul>
    
You could sort the items alphabetically like so:

    $('li').sortElements(function(a, b){
        return $(a).text() > $(b).text() ? 1 : -1;
    });
    
That would result in:

    <ul>
        <li>Apple</li>
        <li>Banana</li>
        <li>Carrot</li>
    </ul>

It also let's you specify what element will be sorted. The current collection's elements will be those referred to as `a` and `b` on each call of the comparator, but you might not want those elements to be the ones to move. E.g. you might want it to be a parent. For example, when sorting a table column, you would sort by the `<td>` elements, but the elements you actually want to move within the DOM are the `<tr>` (each `<td>`'s parent):

    $('td').sortElements(myComparator, function(){
        // Return a reference to the desired element:
        return this.parentNode;
    });
    
See more info here: [http://james.padolsey.com/javascript/sorting-elements-with-jquery/](http://james.padolsey.com/javascript/sorting-elements-with-jquery/).