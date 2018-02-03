## Add_Img_Maps internal data formats

Why did I have two different formats, one for the web form and one for the data
object? The reason is that every field in the input form needed to have its own
name, whereas the &lt;map&gt; elements simply list the co-ordinates together.
(And I based the map element on those.)

So the form needed to separate out the co-ordinates.

### The Input Form

The input form names (and ids) are a set of '-' separated associative array keys.

For example, 'addimgmaps-full-0-3-x' is the x-coordinate of the 4th point of
the 1st area (presumably a polygon shape) on the map for the 'full' image size.

* addimgmaps *plugin name*
  * full *size*
    * 0 *area number*
	  * shape *which shape, eg poly*
	  * href *url*
	  * alt *alternative text*
	  * x,y,r *co-ordinates for circles*
	  * 0,1,... *co-ordinate pairs for rectangles and polygons*
	    * x,y *co-ordinate pairs*

There are also some special values

  * ctrl *the fieldset offering a choice about which maps to edit*
    * rm *this has been deleted*
	* unchanged *this has not been changed since the last time*
	
The metabox initially loads existing maps as empty fieldset elements, with
a data-map attribute containing a JSON object for the map. It is initalised
when the control button is pressed.

#### The Object

The internal Map object itself has a different format, based on the HTML map element,
at it is this which is stored in JSON.

* areas
  * shape
  * href
  * alt
  * coords *list of co-ordinates in order x, y, r*

#### HTML

The HTML map element is documented at 