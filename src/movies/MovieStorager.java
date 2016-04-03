package movies;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;

/**
 * Created by Sofia on 4/3/2016.
 */
public class MovieStorager {
    private static Connection c;

    public static void InsertMovietoDB(Movie m) throws SQLException {
        try {
            System.out.println("****************************************************************");
            // Create a mysql database connection
            String myDriver = "com.mysql.jdbc.Driver";
            String myUrl = "jdbc:mysql://localhost:3306/movies";
            Class.forName(myDriver);
            // TODO: 4/3/2016 Find alternative to hard-coded password 
            Connection conn = DriverManager.getConnection(myUrl, "root", "");

            // The mysql insert statement
            String query = " insert into all_movies (title, year, categories, synopsis, icon_url, cast, " +
                    "director, imdb_url, extended_plot)" + " values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Create the mysql insert PreparedStatement
            PreparedStatement preparedStmt = conn.prepareStatement(query);
            preparedStmt.setString(1, m.getTitle());
            preparedStmt.setInt(2, m.getYear());
            preparedStmt.setString(3, m.getCategories());
            preparedStmt.setString(4, m.getSynopsis());
            preparedStmt.setString(5, m.getIconURL());
            preparedStmt.setString(6, m.getCast());
            preparedStmt.setString(7, m.getDirector());
            preparedStmt.setString(8, m.getImdbURL());
            preparedStmt.setString(9, m.getExtendedPlot());

            // Execute the PreparedStatement
            preparedStmt.execute();

            conn.close();
        } catch (Exception e) {
            System.err.println("Got an exception!");
            System.err.println(e.getMessage());
        }
    }
}
